<?php

declare(strict_types=1);

namespace JMS\SerializerBundle\DependencyInjection;

use Doctrine\Common\Annotations\Reader;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\Exception\RuntimeException;
use JMS\Serializer\Expression\CompilableExpressionEvaluatorInterface;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\Handler\SymfonyUidHandler;
use JMS\Serializer\Metadata\Driver\DocBlockDriver;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Templating\Helper\Helper;
use Symfony\Component\Uid\AbstractUid;
use Symfony\Component\Yaml\Yaml;

/**
 * @internal
 */
final class JMSSerializerExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $rawConfigs, ContainerBuilder $container): void
    {
        $configs = $this->processNestedConfigs($rawConfigs, $container);

        $loader = new XmlFileLoader($container, new FileLocator([__DIR__ . '/../Resources/config/']));
        $loader->load('services.xml');

        if ($configs['profiler']) {
            $loader->load('debug.xml');
        }

        $this->configureMetadataDrivers($container);

        DIUtils::cloneDefinitions($container, array_keys($configs['instances']));

        // twig can be enabled only on one instance at the time
        $container->setParameter('jms_serializer.twig_enabled', $configs['twig_enabled']);

        foreach ($configs['instances'] as $name => $instanceConfigs) {
            $scopedContainer = new ScopedContainer($container, $name);
            $scopedContainer->getDefinition('jms_serializer.serializer')
                ->addTag('jms_serializer.serializer', ['name' => $name]);

            $this->loadInternal($instanceConfigs, $scopedContainer, $configs);
        }

        $container
            ->registerForAutoconfiguration(EventSubscriberInterface::class)
            ->addTag('jms_serializer.event_subscriber');

        $container
            ->registerForAutoconfiguration(SubscribingHandlerInterface::class)
            ->addTag('jms_serializer.subscribing_handler');
    }

    private function configureMetadataDrivers(ContainerBuilder $container): void
    {
        // The old annotation driver service is now always removed from the container in favor of the combined annotation/attribute driver
        $container->removeDefinition('jms_serializer.metadata.annotation_driver');

        /*
         * Build the sorted list of metadata drivers based on the environment. The final order should be:
         *
         * - YAML Driver
         * - XML Driver
         * - Annotations/Attributes Driver
         * - Null (Fallback) Driver
         */
        $metadataDrivers = [];

        if (class_exists(Yaml::class)) {
            $metadataDrivers[] = new Reference('jms_serializer.metadata.yaml_driver');
        } else {
            $container->removeDefinition('jms_serializer.metadata.yaml_driver');
        }

        // The XML driver is always available
        $metadataDrivers[] = new Reference('jms_serializer.metadata.xml_driver');

        // The combined annotation/attribute driver is available if `doctrine/annotations` is installed or when running PHP 8
        if (interface_exists(Reader::class) || PHP_VERSION_ID >= 80000) {
            $metadataDrivers[] = new Reference('jms_serializer.metadata.annotation_or_attribute_driver');
        } else {
            $container->removeDefinition('jms_serializer.metadata.annotation_or_attribute_driver');
        }

        // The null driver is always available
        $metadataDrivers[] = new Reference('jms_serializer.metadata.null_driver');

        $container
            ->getDefinition('jms_serializer.metadata_driver')
            ->replaceArgument(0, $metadataDrivers);
    }

    /**
     * @param array $rawConfigs
     * @param ContainerBuilder $container
     *
     * @return array
     */
    private function loadConfigArray(array $rawConfigs, ContainerBuilder $container): array
    {
        $configs = $this->processConfiguration($this->getConfiguration($rawConfigs, $container), $rawConfigs);
        $defConf = $configs;
        unset($defConf['instances']);
        $configs['instances'] = array_merge(['default' => $defConf], $configs['instances']);

        return $configs;
    }

    private function loadInternal(array $config, ScopedContainer $container, array $mainConfig): void
    {
        // Built-in handlers.
        $container->getDefinition('jms_serializer.datetime_handler')
            ->replaceArgument(0, $config['handlers']['datetime']['default_format'])
            ->replaceArgument(1, $config['handlers']['datetime']['default_timezone'])
            ->replaceArgument(2, $config['handlers']['datetime']['cdata']);

        $container->getDefinition('jms_serializer.array_collection_handler')
            ->replaceArgument(0, $config['handlers']['array_collection']['initialize_excluded']);

        if (class_exists(SymfonyUidHandler::class) && class_exists(AbstractUid::class)) {
            $container->getDefinition('jms_serializer.symfony_uid_handler')
                ->replaceArgument(0, $config['handlers']['symfony_uid']['default_format'])
                ->replaceArgument(1, $config['handlers']['symfony_uid']['cdata']);
        } else {
            $container->removeDefinition('jms_serializer.symfony_uid_handler');
        }

        // Built-in subscribers.
        $container->getDefinition('jms_serializer.doctrine_proxy_subscriber')
            ->replaceArgument(0, !$config['subscribers']['doctrine_proxy']['initialize_virtual_types'])
            ->replaceArgument(1, $config['subscribers']['doctrine_proxy']['initialize_excluded']);

        // Built-in object constructor.
        $container->getDefinition('jms_serializer.doctrine_object_constructor')
            ->replaceArgument(2, $config['object_constructors']['doctrine']['fallback_strategy']);

        // property naming
        $container->getDefinition('jms_serializer.camel_case_naming_strategy')
            ->replaceArgument(0, $config['property_naming']['separator'])
            ->replaceArgument(1, $config['property_naming']['lower_case']);

        if (!empty($config['property_naming']['id'])) {
            $container->setAlias('jms_serializer.naming_strategy', $config['property_naming']['id']);
        }

        if (!class_exists(Helper::class)) {
            $container->removeDefinition('jms_serializer.templating.helper.serializer');
        }

        $bundles = $container->getParameter('kernel.bundles');

        // remove twig services if the bundle is not loaded or if we are configuring an instance fof which twig is not enabled
        // only one instance can have twig enabled
        if (!isset($bundles['TwigBundle']) || $mainConfig['twig_enabled'] !== $container->getInstanceName()) {
            $container->removeDefinition('jms_serializer.twig_extension.serializer');
            $container->removeDefinition('jms_serializer.twig_extension.runtime_serializer');
            $container->removeDefinition('jms_serializer.twig_extension.serializer_runtime_helper');
        }

        if (!empty($config['expression_evaluator']['id'])) {
            $evaluator = new Reference($config['expression_evaluator']['id']);

            $container
                ->getDefinition('jms_serializer.deserialization_graph_navigator_factory')
                ->replaceArgument(5, $evaluator);

            $container
                ->getDefinition('jms_serializer.serialization_graph_navigator_factory')
                ->replaceArgument(4, $evaluator);

            $container
                ->getDefinition('jms_serializer.accessor_strategy.default')
                ->replaceArgument(0, $evaluator);

            if (is_a($container->findDefinition($config['expression_evaluator']['id'])->getClass(), CompilableExpressionEvaluatorInterface::class, true)) {
                try {
                    $container
                        ->getDefinition('jms_serializer.metadata.yaml_driver')
                        ->replaceArgument(3, $evaluator);
                } catch (ServiceNotFoundException $exception) {
                    // Removed by conditional checks earlier
                }

                $container
                    ->getDefinition('jms_serializer.metadata.xml_driver')
                    ->replaceArgument(3, $evaluator);

                try {
                    $container
                        ->getDefinition('jms_serializer.metadata.annotation_or_attribute_driver')
                        ->replaceArgument(2, $evaluator);
                } catch (ServiceNotFoundException $exception) {
                    // Removed by conditional checks earlier
                }
            }
        } else {
            $container->removeDefinition('jms_serializer.expression_evaluator');
        }

        // metadata
        if ('none' === $config['metadata']['cache']) {
            $container->removeAlias('jms_serializer.metadata.cache');
            $container->removeDefinition('jms_serializer.cache.cache_clearer');
        } elseif ('file' === $config['metadata']['cache']) {
            $instance = $container->getInstanceName();

            // make sure that the cache dir is different for each instance
            $dirParam = $config['metadata']['file_cache']['dir'] ?: '%kernel.cache_dir%/jms_serializer' . ($instance ? '_' . $instance : '');

            $container->getDefinition('jms_serializer.metadata.cache.file_cache')
                ->replaceArgument(0, $dirParam);

            $dir = $container->getParameterBag()->resolveValue($dirParam);
            if (!is_dir($dir) && !@mkdir($dir, 0777, true) && !is_dir($dir)) {
                throw new RuntimeException(sprintf('Could not create cache directory "%s".', $dir));
            }
        } else {
            $container->setAlias('jms_serializer.metadata.cache', new Alias($config['metadata']['cache'], false));
        }

        if (false === $config['metadata']['infer_types_from_doctrine_metadata']) {
            $container->removeDefinition('jms_serializer.metadata.doctrine_type_driver');
            $container->removeDefinition('jms_serializer.metadata.doctrine_doctrine_phpcr_type_driver');
        }

        if (false === $config['object_constructors']['doctrine']['enabled']) {
            $container->removeDefinition('jms_serializer.doctrine_object_constructor');
            $container->removeDefinition('jms_serializer.doctrine_doctrine_phpcr__object_constructor');
        }

        if ($config['metadata']['infer_types_from_doc_block'] && class_exists(DocBlockDriver::class)) {
            $container->getDefinition('jms_serializer.metadata.doc_block_driver')
                ->setDecoratedService('jms_serializer.metadata_driver')
                ->setPublic(false);
        } else {
            $container->removeDefinition('jms_serializer.metadata.doc_block_driver');
        }

        // enable the typed props reader
        $container->getDefinition('jms_serializer.metadata.typed_properties_driver')
            ->setDecoratedService('jms_serializer.metadata_driver')
            ->setPublic(false);

        if ($config['enum_support']) {
            $container->getDefinition('jms_serializer.metadata.enum_driver')
                ->setDecoratedService('jms_serializer.metadata_driver', null, 50)
                ->setPublic(false);
        } else {
            $container->removeDefinition('jms_serializer.metadata.enum_driver');
            $container->removeDefinition('jms_serializer.enum_handler');
            $container->removeDefinition('jms_serializer.enum_subscriber');
        }

        // enable the default value property reader on php 8.0+
        if (PHP_VERSION_ID >= 80000 && $config['default_value_property_reader_support']) {
            $container->getDefinition('jms_serializer.metadata.default_value_property_driver')
                ->setDecoratedService('jms_serializer.metadata_driver')
                ->setPublic(false);
        } else {
            $container->removeDefinition('jms_serializer.metadata.default_value_property_driver');
        }

        $container
            ->getDefinition('jms_serializer.metadata_factory')
            ->replaceArgument(2, $config['metadata']['debug'])
            ->addMethodCall('setIncludeInterfaces', [$config['metadata']['include_interfaces']]);

        // warmup
        if (!empty($config['metadata']['warmup']['paths']['included']) && class_exists(Finder::class)) {
            $container
                ->getDefinition('jms_serializer.cache.cache_warmer')
                ->replaceArgument(0, $config['metadata']['warmup']['paths']['included'])
                ->replaceArgument(2, $config['metadata']['warmup']['paths']['excluded']);
        } else {
            $container->removeDefinition('jms_serializer.cache.cache_warmer');
        }

        $directories = $this->detectMetadataDirectories($config['metadata'], $container->getParameter('kernel.bundles_metadata'));

        $container
            ->getDefinition('jms_serializer.metadata.file_locator')
            ->replaceArgument(0, $directories);

        // the profiler setting is global
        if ($mainConfig['profiler']) {
            $container
                ->getDefinition('jms_serializer.data_collector')
                ->replaceArgument(0, $container->getInstanceName())
                ->replaceArgument(1, $directories);
        } else {
            // remove profiler DI defintions if the profiler is not enabled
            array_map([$container, 'removeDefinition'], array_keys($container->findTaggedServiceIds('jms_serializer.profiler')));
        }

        $this->setVisitorOptions($config, $container);

        if ($container->getParameter('kernel.debug') && class_exists(Stopwatch::class)) {
            $container->getDefinition('jms_serializer.stopwatch_subscriber')
                ->replaceArgument(1, sprintf('jms_serializer.%s', $container->getInstanceName()));
        } else {
            $container->removeDefinition('jms_serializer.stopwatch_subscriber');
        }

        $this->setContextFactories($container, $config);
    }

    /**
     * @return ConfigurationInterface
     */
    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        return new Configuration($container->getParameterBag()->resolveValue('%kernel.debug%'));
    }

    private function processNestedConfigs(array $rawConfigs, ContainerBuilder $container): array
    {
        $configs = $this->loadConfigArray($rawConfigs, $container);

        $needReConfig = false;
        foreach ($configs['instances'] as $name => $value) {
            // if we inherit from the default configs, we merge/append the default confs into the current instance
            if (!empty($value['inherit'])) {
                // the twig setting is not per instance, so we need to remove it before merging the configs
                unset($configs['instances']['default']['twig_enabled']);
                unset($configs['instances']['default']['profiler']);
                array_unshift($rawConfigs, [
                    'instances' => [$name => $configs['instances']['default']],
                ]);
                $needReConfig = true;
            }
        }

        // if we had to merge at least one config, we need to re-merge them
        if ($needReConfig) {
            $configs = $this->loadConfigArray($rawConfigs, $container);
        }

        unset($value);

        return $configs;
    }

    private function setContextFactories(ScopedContainer $container, array $config): void
    {
        // context factories
        $services = [
            'serialization' => 'jms_serializer.configured_serialization_context_factory',
            'deserialization' => 'jms_serializer.configured_deserialization_context_factory',
        ];
        foreach ($services as $configKey => $serviceId) {
            $contextFactory = $container->getDefinition($serviceId);

            if (isset($config['default_context'][$configKey]['id'])) {
                $container->setAlias('jms_serializer.' . $configKey . '_context_factory', new Alias($config['default_context'][$configKey]['id'], true));
                $container->setAlias('JMS\\Serializer\\ContextFactory\\' . ucfirst($configKey) . 'ContextFactoryInterface', new Alias($config['default_context'][$configKey]['id'], true));
                $container->removeDefinition($serviceId);
                continue;
            }

            if (isset($config['default_context'][$configKey]['version'])) {
                $contextFactory->addMethodCall('setVersion', [$config['default_context'][$configKey]['version']]);
            }

            if (isset($config['default_context'][$configKey]['serialize_null'])) {
                $contextFactory->addMethodCall('setSerializeNulls', [$config['default_context'][$configKey]['serialize_null']]);
            }

            if (!empty($config['default_context'][$configKey]['attributes'])) {
                $contextFactory->addMethodCall('setAttributes', [$config['default_context'][$configKey]['attributes']]);
            }

            if (!empty($config['default_context'][$configKey]['groups'])) {
                $contextFactory->addMethodCall('setGroups', [$config['default_context'][$configKey]['groups']]);
            }

            if (!empty($config['default_context'][$configKey]['enable_max_depth_checks'])) {
                $contextFactory->addMethodCall('enableMaxDepthChecks');
            }
        }
    }

    private function setVisitorOptions(array $config, ScopedContainer $container): void
    {
        // json (serialization)
        if (isset($config['visitors']['json_serialization']['options'])) {
            $container->getDefinition('jms_serializer.json_serialization_visitor')
                ->addMethodCall('setOptions', [$config['visitors']['json_serialization']['options']]);
        }

        if (isset($config['visitors']['json_serialization']['depth'])) {
            $container->getDefinition('jms_serializer.json_serialization_visitor')
                ->addMethodCall('setDepth', [$config['visitors']['json_serialization']['depth']]);
        }

        // json (deserialization)
        if (isset($config['visitors']['json_deserialization']['options'])) {
            $container->getDefinition('jms_serializer.json_deserialization_visitor')
                ->addMethodCall('setOptions', [$config['visitors']['json_deserialization']['options']]);
        }

        $container->getDefinition('jms_serializer.json_deserialization_visitor')
            ->replaceArgument(0, (bool) $config['visitors']['json_deserialization']['strict']);

        // xml (serialization)
        if (!empty($config['visitors']['xml_serialization']['default_root_name'])) {
            $container->getDefinition('jms_serializer.xml_serialization_visitor')
                ->addMethodCall('setDefaultRootName', [
                    $config['visitors']['xml_serialization']['default_root_name'],
                    $config['visitors']['xml_serialization']['default_root_ns'],
                ]);
        }

        if (!empty($config['visitors']['xml_serialization']['version'])) {
            $container->getDefinition('jms_serializer.xml_serialization_visitor')
                ->addMethodCall('setDefaultVersion', [$config['visitors']['xml_serialization']['version']]);
        }

        if (!empty($config['visitors']['xml_serialization']['encoding'])) {
            $container->getDefinition('jms_serializer.xml_serialization_visitor')
                ->addMethodCall('setDefaultEncoding', [$config['visitors']['xml_serialization']['encoding']]);
        }

        if (!empty($config['visitors']['xml_serialization']['format_output'])) {
            $container->getDefinition('jms_serializer.xml_serialization_visitor')
                ->addMethodCall('setFormatOutput', [$config['visitors']['xml_serialization']['format_output']]);
        }

        // xml (deserialization)
        if (!empty($config['visitors']['xml_deserialization']['doctype_whitelist'])) {
            $container->getDefinition('jms_serializer.xml_deserialization_visitor')
                ->addMethodCall('setDoctypeWhitelist', [$config['visitors']['xml_deserialization']['doctype_whitelist']]);
        }

        if (!empty($config['visitors']['xml_deserialization']['external_entities'])) {
            $container->getDefinition('jms_serializer.xml_deserialization_visitor')
                ->addMethodCall('enableExternalEntities', [$config['visitors']['xml_deserialization']['external_entities']]);
        }

        if (!empty($config['visitors']['xml_deserialization']['options'])) {
            $container->getDefinition('jms_serializer.xml_deserialization_visitor')
                ->addMethodCall('setOptions', [$config['visitors']['xml_deserialization']['options']]);
        }
    }

    private function detectMetadataDirectories(array $metadata, array $bundlesMetadata): array
    {
        $directories = [];
        if ($metadata['auto_detection']) {
            foreach ($bundlesMetadata as $bundle) {
                if (is_dir($dir = $bundle['path'] . '/Resources/config/serializer') || is_dir($dir = $bundle['path'] . '/config/serializer')) {
                    $directories[$bundle['namespace']] = $dir;
                }
            }
        }

        foreach ($metadata['directories'] as $directory) {
            $directory['path'] = rtrim(str_replace('\\', '/', $directory['path']), '/');

            if ('@' === $directory['path'][0]) {
                $pathParts = explode('/', $directory['path'], 2);
                $bundleName = substr($pathParts[0], 1);

                if (!isset($bundlesMetadata[$bundleName])) {
                    throw new RuntimeException(sprintf('The bundle "%s" has not been registered with AppKernel. Available bundles: %s', $bundleName, implode(', ', array_keys($bundlesMetadata))));
                }

                $directory['path'] = $bundlesMetadata[$bundleName]['path'] . substr($directory['path'], strlen('@' . $bundleName));
            }

            $dir = rtrim($directory['path'], '\\/');
            if (!file_exists($dir)) {
                throw new RuntimeException(sprintf('The metadata directory "%s" does not exist for the namespace "%s"', $dir, $directory['namespace_prefix']));
            }

            $directories[rtrim($directory['namespace_prefix'], '\\')] = $dir;
        }

        return $directories;
    }
}

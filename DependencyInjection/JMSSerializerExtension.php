<?php

namespace JMS\SerializerBundle\DependencyInjection;

use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\Exception\RuntimeException;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;
use Symfony\Component\Stopwatch\Stopwatch;
use JMS\Serializer\Metadata\Driver\TypedPropertiesDriver;
use Symfony\Component\Templating\Helper\Helper;

class JMSSerializerExtension extends ConfigurableExtension
{
    public function loadInternal(array $config, ContainerBuilder $container)
    {
        $container
            ->registerForAutoconfiguration(EventSubscriberInterface::class)
            ->addTag('jms_serializer.event_subscriber');

        $container
            ->registerForAutoconfiguration(SubscribingHandlerInterface::class)
            ->addTag('jms_serializer.subscribing_handler');

        $loader = new XmlFileLoader($container, new FileLocator(array(
            __DIR__ . '/../Resources/config/')));
        $loader->load('services.xml');

        // Built-in handlers.
        $container->getDefinition('jms_serializer.datetime_handler')
            ->addArgument($config['handlers']['datetime']['default_format'])
            ->addArgument($config['handlers']['datetime']['default_timezone'])
            ->addArgument($config['handlers']['datetime']['cdata']);

        $container->getDefinition('jms_serializer.array_collection_handler')
            ->replaceArgument(0, $config['handlers']['array_collection']['initialize_excluded']);

        // Built-in subscribers.
        $container->getDefinition('jms_serializer.doctrine_proxy_subscriber')
            ->replaceArgument(0, !$config['subscribers']['doctrine_proxy']['initialize_virtual_types'])
            ->replaceArgument(1, $config['subscribers']['doctrine_proxy']['initialize_excluded']);

        // Built-in object constructor.
        $container->getDefinition('jms_serializer.doctrine_object_constructor')
            ->replaceArgument(2, $config['object_constructors']['doctrine']['fallback_strategy']);

        // property naming
        $container
            ->getDefinition('jms_serializer.camel_case_naming_strategy')
            ->addArgument($config['property_naming']['separator'])
            ->addArgument($config['property_naming']['lower_case']);

        if (!empty($config['property_naming']['id'])) {
            $container->setAlias('jms_serializer.naming_strategy', $config['property_naming']['id']);
        }

        if (!class_exists(Helper::class)) {
            $container->removeDefinition('jms_serializer.templating.helper.serializer');
        }

        $bundles = $container->getParameter('kernel.bundles');

        if (!isset($bundles['TwigBundle'])) {
            $container->removeDefinition('jms_serializer.twig_extension.serializer');
            $container->removeDefinition('jms_serializer.twig_extension.serializer_runtime_helper');
        }

        if (!empty($config['expression_evaluator']['id'])) {
            $container
                ->getDefinition('jms_serializer.deserialization_graph_navigator_factory')
                ->replaceArgument(5, new Reference($config['expression_evaluator']['id']));

            $container
                ->getDefinition('jms_serializer.serialization_graph_navigator_factory')
                ->replaceArgument(4, new Reference($config['expression_evaluator']['id']));

            $container
                ->getDefinition('jms_serializer.accessor_strategy.default')
                ->setArgument(0, new Reference($config['expression_evaluator']['id']));

        } else {
            $container->removeDefinition('jms_serializer.expression_evaluator');
        }

        // metadata
        if ('none' === $config['metadata']['cache']) {
            $container->removeAlias('jms_serializer.metadata.cache');
        } elseif ('file' === $config['metadata']['cache']) {
            $container
                ->getDefinition('jms_serializer.metadata.cache.file_cache')
                ->replaceArgument(0, $config['metadata']['file_cache']['dir']);

            $dir = $container->getParameterBag()->resolveValue($config['metadata']['file_cache']['dir']);
            if (!is_dir($dir) && !@mkdir($dir, 0777, true) && !is_dir($dir)) {
                throw new RuntimeException(sprintf('Could not create cache directory "%s".', $dir));
            }
        } else {
            $container->setAlias('jms_serializer.metadata.cache', new Alias($config['metadata']['cache'], false));
        }

        if ($config['metadata']['infer_types_from_doctrine_metadata'] === false) {
            $container->setParameter('jms_serializer.infer_types_from_doctrine_metadata', false);
        }

        if (PHP_VERSION_ID >= 70400 && class_exists(TypedPropertiesDriver::class)) {
            $container->getDefinition('jms_serializer.metadata.typed_properties_driver')
                ->setDecoratedService('jms_serializer.metadata_driver')
                ->setPublic(false)
            ;
        }

        $container
            ->getDefinition('jms_serializer.metadata_factory')
            ->replaceArgument(2, $config['metadata']['debug']);

        // warmup
        if (!empty($config['metadata']['warmup']['paths']['included']) && class_exists(Finder::class)) {
            $container
                ->getDefinition('jms_serializer.cache.cache_warmer')
                ->replaceArgument(0, $config['metadata']['warmup']['paths']['included'])
                ->replaceArgument(2, $config['metadata']['warmup']['paths']['excluded']);
        } else {
            $container->removeDefinition('jms_serializer.cache.cache_warmer');
        }

        // directories
        $directories = array();
        if ($config['metadata']['auto_detection']) {
            foreach ($bundles as $name => $class) {
                $ref = new \ReflectionClass($class);

                $dir = dirname($ref->getFileName()) . '/Resources/config/serializer';
                if (file_exists($dir)) {
                    $directories[$ref->getNamespaceName()] = $dir;
                }
            }
        }
        foreach ($config['metadata']['directories'] as $directory) {
            $directory['path'] = rtrim(str_replace('\\', '/', $directory['path']), '/');

            if ('@' === $directory['path'][0]) {
                $pathParts = explode('/', $directory['path']);
                $bundleName = substr($pathParts[0], 1);

                if (!isset($bundles[$bundleName])) {
                    throw new RuntimeException(sprintf('The bundle "%s" has not been registered with AppKernel. Available bundles: %s', $bundleName, implode(', ', array_keys($bundles))));
                }

                $ref = new \ReflectionClass($bundles[$bundleName]);
                $directory['path'] = dirname($ref->getFileName()) . substr($directory['path'], strlen('@' . $bundleName));
            }

            $dir = rtrim($directory['path'], '\\/');
            if (!file_exists($dir)) {
                throw new RuntimeException(sprintf('The metadata directory "%s" does not exist for the namespace "%s"', $dir, $directory['namespace_prefix']));
            }

            $directories[rtrim($directory['namespace_prefix'], '\\')] = $dir;
        }
        $container
            ->getDefinition('jms_serializer.metadata.file_locator')
            ->replaceArgument(0, $directories);

        $this->setVisitorOptions($config, $container);

        if (!$container->getParameter('kernel.debug') || !class_exists(Stopwatch::class)) {
            $container->removeDefinition('jms_serializer.stopwatch_subscriber');
        }

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

    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        return new Configuration($container->getParameterBag()->resolveValue('%kernel.debug%'));
    }

    private function setVisitorOptions(array $config, ContainerBuilder $container): void
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
}

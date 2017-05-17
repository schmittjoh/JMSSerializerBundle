<?php

/*
 * Copyright 2011 Johannes M. Schmitt <schmittjoh@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace JMS\SerializerBundle\DependencyInjection;

use JMS\Serializer\Exception\RuntimeException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class JMSSerializerExtension extends ConfigurableExtension
{
    public function loadInternal(array $config, ContainerBuilder $container)
    {
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

        if ($config['property_naming']['enable_cache']) {
            $container
                ->getDefinition('jms_serializer.cache_naming_strategy')
                ->addArgument(new Reference((string)$container->getAlias('jms_serializer.naming_strategy')));
            $container->setAlias('jms_serializer.naming_strategy', 'jms_serializer.cache_naming_strategy');
        }

        $bundles = $container->getParameter('kernel.bundles');

        if (!empty($config['expression_evaluator']['id'])) {
            $container
                ->getDefinition('jms_serializer.serializer')
                ->replaceArgument(7, new Reference($config['expression_evaluator']['id']));

            $container
                ->setAlias('jms_serializer.accessor_strategy', 'jms_serializer.accessor_strategy.expression');

        } else {
            $container->removeDefinition('jms_serializer.expression_evaluator');
            $container->removeDefinition('jms_serializer.accessor_strategy.expression');
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

        $container
            ->getDefinition('jms_serializer.metadata_factory')
            ->replaceArgument(2, $config['metadata']['debug']);

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

        $container->setParameter('jms_serializer.xml_deserialization_visitor.doctype_whitelist', $config['visitors']['xml']['doctype_whitelist']);
        $container->setParameter('jms_serializer.xml_serialization_visitor.format_output', $config['visitors']['xml']['format_output']);
        $container->setParameter('jms_serializer.json_serialization_visitor.options', $config['visitors']['json']['options']);

        if (!$container->getParameter('kernel.debug')) {
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
                $container->setAlias('jms_serializer.' . $configKey . '_context_factory', $config['default_context'][$configKey]['id']);
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
}

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

namespace JMS\SerializerBundle\Tests\DependencyInjection;

use JMS\SerializerBundle\DependencyInjection\Configuration;
use JMS\SerializerBundle\JMSSerializerBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ConfigurationTest extends TestCase
{
    private function getContainer(array $configs = array())
    {
        $container = new ContainerBuilder();

        $container->setParameter('kernel.debug', true);
        $container->setParameter('kernel.cache_dir', sys_get_temp_dir() . '/serializer');
        $container->setParameter('kernel.bundles', array('JMSSerializerBundle' => 'JMS\SerializerBundle\JMSSerializerBundle'));

        $bundle = new JMSSerializerBundle();

        $extension = $bundle->getContainerExtension();
        $extension->load($configs, $container);

        return $container;
    }

    public function testConfig()
    {
        $ref = new JMSSerializerBundle();
        $container = $this->getContainer([
            [
                'metadata' => [
                    'directories' => [
                        'foo' => [
                            'namespace_prefix' => 'JMSSerializerBundleNs1',
                            'path' => '@JMSSerializerBundle',
                        ],
                        'bar' => [
                            'namespace_prefix' => 'JMSSerializerBundleNs2',
                            'path' => '@JMSSerializerBundle/Resources/config',
                        ],
                    ]
                ]
            ],
        ]);

        $directories = $container->getDefinition('jms_serializer.metadata.file_locator')->getArgument(0);

        $this->assertEquals($ref->getPath(), $directories['JMSSerializerBundleNs1']);
        $this->assertEquals($ref->getPath() . '/Resources/config', $directories['JMSSerializerBundleNs2']);
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testWrongObjectConstructorFallbackStrategyTriggersException()
    {
        $processor = new Processor();
        $processor->processConfiguration(new Configuration(true), [
            'jms_serializer' => [
                'object_constructors' => [
                    'doctrine' => [
                        'fallback_strategy' => "foo",
                    ],
                ]
            ]
        ]);
    }

    public function testConfigComposed()
    {
        $ref = new JMSSerializerBundle();
        $container = $this->getContainer([
            [
                'metadata' => [
                    'directories' => [
                        'foo' => [
                            'namespace_prefix' => 'JMSSerializerBundleNs1',
                            'path' => '@JMSSerializerBundle',
                        ],
                    ]
                ]
            ],
            [
                'metadata' => [
                    'directories' => [
                        [
                            'name' => 'foo',
                            'namespace_prefix' => 'JMSSerializerBundleNs2',
                            'path' => '@JMSSerializerBundle/Resources/config',
                        ],
                    ]
                ]
            ],
        ]);

        $directories = $container->getDefinition('jms_serializer.metadata.file_locator')->getArgument(0);

        $this->assertArrayNotHasKey('JMSSerializerBundleNs1', $directories);
        $this->assertEquals($ref->getPath() . '/Resources/config', $directories['JMSSerializerBundleNs2']);
    }

    public function testContextDefaults()
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(true), []);

        $this->assertArrayHasKey('default_context', $config);
        foreach (['serialization', 'deserialization'] as $item) {
            $this->assertArrayHasKey($item, $config['default_context']);

            $defaultContext = $config['default_context'][$item];

            $this->assertTrue(is_array($defaultContext['attributes']));
            $this->assertEmpty($defaultContext['attributes']);

            $this->assertTrue(is_array($defaultContext['groups']));
            $this->assertEmpty($defaultContext['groups']);

            $this->assertArrayNotHasKey('version', $defaultContext);
            $this->assertArrayNotHasKey('serialize_null', $defaultContext);
        }
    }

    public function testContextValues()
    {
        $configArray = array(
            'serialization' => array(
                'version' => 3,
                'serialize_null' => true,
                'attributes' => ['foo' => 'bar'],
                'groups' => ['Baz'],
                'enable_max_depth_checks' => false,
            ),
            'deserialization' => array(
                'version' => "5.5",
                'serialize_null' => false,
                'attributes' => ['foo' => 'bar'],
                'groups' => ['Baz'],
                'enable_max_depth_checks' => true,
            )
        );

        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(true), [
            'jms_serializer' => [
                'default_context' => $configArray
            ]
        ]);

        $this->assertArrayHasKey('default_context', $config);
        foreach (['serialization', 'deserialization'] as $configKey) {
            $this->assertArrayHasKey($configKey, $config['default_context']);

            $values = $config['default_context'][$configKey];
            $confArray = $configArray[$configKey];

            $this->assertSame($values['version'], $confArray['version']);
            $this->assertSame($values['serialize_null'], $confArray['serialize_null']);
            $this->assertSame($values['attributes'], $confArray['attributes']);
            $this->assertSame($values['groups'], $confArray['groups']);
            $this->assertSame($values['enable_max_depth_checks'], $confArray['enable_max_depth_checks']);
        }
    }

    public function testConfigNormalization()
    {
        $configArray = [
            'default_context' => [
                'serialization' => 'the.serialization.factory.context',
                'deserialization' => 'the.deserialization.factory.context',
            ],
            'property_naming' => 'property.mapping.service',
            'expression_evaluator' => 'expression_evaluator.service',
        ];

        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(true), [
            'jms_serializer' => $configArray
        ]);

        $this->assertArrayHasKey('default_context', $config);
        $this->assertArrayHasKey('serialization', $config['default_context']);
        $this->assertArrayHasKey('deserialization', $config['default_context']);
        $this->assertArrayHasKey('id', $config['default_context']['serialization']);
        $this->assertArrayHasKey('id', $config['default_context']['deserialization']);

        $this->assertSame($configArray['default_context']['serialization'], $config['default_context']['serialization']['id']);
        $this->assertSame($configArray['default_context']['deserialization'], $config['default_context']['deserialization']['id']);

        $this->assertArrayHasKey('property_naming', $config);
        $this->assertArrayHasKey('expression_evaluator', $config);
        $this->assertArrayHasKey('id', $config['property_naming']);
        $this->assertArrayHasKey('id', $config['expression_evaluator']);
        $this->assertSame($configArray['property_naming'], $config['property_naming']['id']);
        $this->assertSame($configArray['expression_evaluator'], $config['expression_evaluator']['id']);
    }

    public function testContextNullValues()
    {
        $configArray = array(
            'serialization' => array(
                'version' => null,
                'serialize_null' => null,
                'attributes' => null,
                'groups' => null,
            ),
            'deserialization' => array(
                'version' => null,
                'serialize_null' => null,
                'attributes' => null,
                'groups' => null,
            )
        );

        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(true), [
            'jms_serializer' => [
                'default_context' => $configArray
            ]
        ]);

        $this->assertArrayHasKey('default_context', $config);
        foreach (['serialization', 'deserialization'] as $configKey) {
            $this->assertArrayHasKey($configKey, $config['default_context']);

            $defaultContext = $config['default_context'][$configKey];

            $this->assertTrue(is_array($defaultContext['attributes']));
            $this->assertEmpty($defaultContext['attributes']);

            $this->assertTrue(is_array($defaultContext['groups']));
            $this->assertEmpty($defaultContext['groups']);

            $this->assertArrayNotHasKey('version', $defaultContext);
            $this->assertArrayNotHasKey('serialize_null', $defaultContext);
        }
    }

    public function testDefaultDateFormat()
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(true), []);

        $this->assertEquals(\DateTime::ATOM, $config['handlers']['datetime']['default_format']);
    }
}

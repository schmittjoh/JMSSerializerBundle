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

use Doctrine\Common\Annotations\AnnotationReader;
use JMS\Serializer\SerializationContext;
use JMS\SerializerBundle\JMSSerializerBundle;
use JMS\SerializerBundle\Tests\DependencyInjection\Fixture\ObjectUsingExpressionLanguage;
use JMS\SerializerBundle\Tests\DependencyInjection\Fixture\ObjectUsingExpressionProperties;
use JMS\SerializerBundle\Tests\DependencyInjection\Fixture\SimpleObject;
use JMS\SerializerBundle\Tests\DependencyInjection\Fixture\VersionedObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class JMSSerializerExtensionTest extends TestCase
{
    protected function setUp()
    {
        $this->clearTempDir();
    }

    protected function tearDown()
    {
        $this->clearTempDir();
    }

    private function clearTempDir()
    {
        // clear temporary directory
        $dir = sys_get_temp_dir() . '/serializer';
        if (is_dir($dir)) {
            foreach (new \RecursiveDirectoryIterator($dir) as $file) {
                $filename = $file->getFileName();
                if ('.' === $filename || '..' === $filename) {
                    continue;
                }

                @unlink($file->getPathName());
            }

            @rmdir($dir);
        }
    }

    public function testHasContextFactories()
    {
        $container = $this->getContainerForConfig(array(array()));

        $factory = $container->get('jms_serializer.serialization_context_factory');
        $this->assertInstanceOf('JMS\Serializer\ContextFactory\SerializationContextFactoryInterface', $factory);

        $factory = $container->get('jms_serializer.deserialization_context_factory');
        $this->assertInstanceOf('JMS\Serializer\ContextFactory\DeserializationContextFactoryInterface', $factory);
    }

    public function testSerializerContextFactoriesAreSet()
    {
        $container = $this->getContainerForConfig(array(array()));

        $def = $container->getDefinition('jms_serializer');
        $calls = $def->getMethodCalls();

        $this->assertCount(2, $calls);

        $serializationCall = $calls[0];
        $this->assertEquals('setSerializationContextFactory', $serializationCall[0]);
        $this->assertEquals('jms_serializer.serialization_context_factory', (string)$serializationCall[1][0]);

        $serializationCall = $calls[1];
        $this->assertEquals('setDeserializationContextFactory', $serializationCall[0]);
        $this->assertEquals('jms_serializer.deserialization_context_factory', (string)$serializationCall[1][0]);
    }

    public function testSerializerContextFactoriesWithId()
    {
        $config = array(
            'default_context' => array(
                'serialization' => array(
                    'id' => 'foo'
                ),
                'deserialization' => array(
                    'id' => 'bar'
                )
            )
        );

        $foo = new Definition('stdClass');
        $foo->setPublic(true);
        $bar = new Definition('stdClass');
        $bar->setPublic(true);

        $container = $this->getContainerForConfig(array($config), function (ContainerBuilder $containerBuilder) use ($foo, $bar) {
            $containerBuilder->setDefinition('foo', $foo);
            $containerBuilder->setDefinition('bar', $bar);
        });

        $def = $container->getDefinition('jms_serializer');
        $calls = $def->getMethodCalls();

        $this->assertCount(2, $calls);

        $serializationCall = $calls[0];
        $this->assertEquals('setSerializationContextFactory', $serializationCall[0]);
        $this->assertEquals('foo', (string)$serializationCall[1][0]);

        $serializationCall = $calls[1];
        $this->assertEquals('setDeserializationContextFactory', $serializationCall[0]);
        $this->assertEquals('bar', (string)$serializationCall[1][0]);

        $this->assertEquals('bar', (string)$container->getAlias('jms_serializer.deserialization_context_factory'));
        $this->assertEquals('foo', (string)$container->getAlias('jms_serializer.serialization_context_factory'));
    }

    public function testLoadWithoutTranslator()
    {
        $container = $this->getContainerForConfig(array(array()), function (ContainerBuilder $containerBuilder) {
            $containerBuilder->set('translator', null);
            $containerBuilder->getDefinition('jms_serializer.form_error_handler')->setPublic(true);
        });

        $def = $container->getDefinition('jms_serializer.form_error_handler');
        $this->assertSame(null, $def->getArgument(0));
    }

    public function testConfiguringContextFactories()
    {
        $container = $this->getContainerForConfig(array(array()));

        $def = $container->getDefinition('jms_serializer.serialization_context_factory');
        $this->assertCount(0, $def->getMethodCalls());

        $def = $container->getDefinition('jms_serializer.deserialization_context_factory');
        $this->assertCount(0, $def->getMethodCalls());
    }

    public function testConfiguringContextFactoriesWithParams()
    {
        $config = array(
            'default_context' => array(
                'serialization' => array(
                    'version' => 1600,
                    'serialize_null' => true,
                    'attributes' => array('x' => 1720),
                    'groups' => array('Default', 'Registration'),
                    'enable_max_depth_checks' => true,
                ),
                'deserialization' => array(
                    'version' => 1640,
                    'serialize_null' => false,
                    'attributes' => array('x' => 1740),
                    'groups' => array('Default', 'Profile'),
                    'enable_max_depth_checks' => true,
                )
            )
        );

        $container = $this->getContainerForConfig(array($config));
        $services = [
            'serialization' => 'jms_serializer.serialization_context_factory',
            'deserialization' => 'jms_serializer.deserialization_context_factory',
        ];
        foreach ($services as $configKey => $serviceId) {
            $def = $container->getDefinition($serviceId);
            $values = $config['default_context'][$configKey];

            $this->assertSame($values['version'], $this->getDefinitionMethodCall($def, 'setVersion')[0]);
            $this->assertSame($values['serialize_null'], $this->getDefinitionMethodCall($def, 'setSerializeNulls')[0]);
            $this->assertSame($values['attributes'], $this->getDefinitionMethodCall($def, 'setAttributes')[0]);
            $this->assertSame($values['groups'], $this->getDefinitionMethodCall($def, 'setGroups')[0]);
            $this->assertSame($values['groups'], $this->getDefinitionMethodCall($def, 'setGroups')[0]);
            $this->assertSame(array(), $this->getDefinitionMethodCall($def, 'enableMaxDepthChecks'));
        }
    }

    public function testConfiguringContextFactoriesWithNullDefaults()
    {
        $config = array(
            'default_context' => array(
                'serialization' => array(
                    'version' => null,
                    'serialize_null' => null,
                    'attributes' => [],
                    'groups' => null,
                ),
                'deserialization' => array(
                    'version' => null,
                    'serialize_null' => null,
                    'attributes' => null,
                    'groups' => null,
                )
            )
        );

        $container = $this->getContainerForConfig(array($config));
        $services = [
            'serialization' => 'jms_serializer.serialization_context_factory',
            'deserialization' => 'jms_serializer.deserialization_context_factory',
        ];
        foreach ($services as $configKey => $serviceId) {
            $def = $container->getDefinition($serviceId);
            $this->assertCount(0, $def->getMethodCalls());
        }
    }

    private function getDefinitionMethodCall(Definition $def, $method)
    {
        foreach ($def->getMethodCalls() as $call) {
            if ($call[0] === $method) {
                return $call[1];
            }
        }
        return false;
    }

    public function testLoad()
    {
        $container = $this->getContainerForConfig(array(array()), function (ContainerBuilder $container) {
            $container->getDefinition('jms_serializer.doctrine_object_constructor')->setPublic(true);
            $container->getDefinition('jms_serializer.array_collection_handler')->setPublic(true);
            $container->getDefinition('jms_serializer.doctrine_proxy_subscriber')->setPublic(true);
            $container->getAlias('JMS\Serializer\SerializerInterface')->setPublic(true);
            $container->getAlias('JMS\Serializer\ArrayTransformerInterface')->setPublic(true);
        });

        $simpleObject = new SimpleObject('foo', 'bar');
        $versionedObject = new VersionedObject('foo', 'bar');
        $serializer = $container->get('jms_serializer');

        $this->assertTrue($container->has('JMS\Serializer\SerializerInterface'), 'Alias should be defined to allow autowiring');
        $this->assertTrue($container->has('JMS\Serializer\ArrayTransformerInterface'), 'Alias should be defined to allow autowiring');

        $this->assertFalse($container->getDefinition('jms_serializer.array_collection_handler')->getArgument(0));

        // the logic is inverted because arg 0 on doctrine_proxy_subscriber is $skipVirtualTypeInit = false
        $this->assertTrue($container->getDefinition('jms_serializer.doctrine_proxy_subscriber')->getArgument(0));
        $this->assertFalse($container->getDefinition('jms_serializer.doctrine_proxy_subscriber')->getArgument(1));

        $this->assertEquals("null", $container->getDefinition('jms_serializer.doctrine_object_constructor')->getArgument(2));

        // test that all components have been wired correctly
        $this->assertEquals(json_encode(array('name' => 'bar')), $serializer->serialize($versionedObject, 'json'));
        $this->assertEquals($simpleObject, $serializer->deserialize($serializer->serialize($simpleObject, 'json'), get_class($simpleObject), 'json'));
        $this->assertEquals($simpleObject, $serializer->deserialize($serializer->serialize($simpleObject, 'xml'), get_class($simpleObject), 'xml'));

        $this->assertEquals(json_encode(array('name' => 'foo')), $serializer->serialize($versionedObject, 'json', SerializationContext::create()->setVersion('0.0.1')));

        $this->assertEquals(json_encode(array('name' => 'bar')), $serializer->serialize($versionedObject, 'json', SerializationContext::create()->setVersion('1.1.1')));
    }

    public function testLoadWithOptions()
    {
        $container = $this->getContainerForConfig(array(array(
            'subscribers' => [
                'doctrine_proxy' => [
                    'initialize_virtual_types' => true,
                    'initialize_excluded' => true,
                ],
            ],
            'object_constructors' => [
                'doctrine' => [
                    'fallback_strategy' => "exception",
                ],
            ],
            'handlers' => [
                'array_collection' => [
                    'initialize_excluded' => true,
                ],
            ],
        )), function ($container) {
            $container->getDefinition('jms_serializer.doctrine_object_constructor')->setPublic(true);
            $container->getDefinition('jms_serializer.array_collection_handler')->setPublic(true);
            $container->getDefinition('jms_serializer.doctrine_proxy_subscriber')->setPublic(true);
        });

        $this->assertTrue($container->getDefinition('jms_serializer.array_collection_handler')->getArgument(0));

        // the logic is inverted because arg 0 on doctrine_proxy_subscriber is $skipVirtualTypeInit = false
        $this->assertFalse($container->getDefinition('jms_serializer.doctrine_proxy_subscriber')->getArgument(0));
        $this->assertTrue($container->getDefinition('jms_serializer.doctrine_proxy_subscriber')->getArgument(1));

        $this->assertEquals("exception", $container->getDefinition('jms_serializer.doctrine_object_constructor')->getArgument(2));
    }

    public function testLoadExistentMetadataDir()
    {
        $container = $this->getContainerForConfig(array(array(
            'metadata' => [
                'directories' => [
                    'foo' => [
                        'namespace_prefix' => 'foo_ns',
                        'path' => __DIR__,
                    ]
                ]
            ]
        )), function ($container) {
            $container->getDefinition('jms_serializer.metadata.file_locator')->setPublic(true);
        });

        $fileLocatorDef = $container->getDefinition('jms_serializer.metadata.file_locator');
        $directories = $fileLocatorDef->getArgument(0);
        $this->assertEquals(['foo_ns' => __DIR__], $directories);
    }

    public function testWarmUpWithDirs()
    {
        $container = $this->getContainerForConfig([[
            'metadata' => [
                'warmup' => [
                    'paths' => [
                        'included' => ['a'],
                        'excluded' => ['b']
                    ]
                ]
            ]
        ]], function ($container){
            $container->getDefinition('jms_serializer.cache.cache_warmer')->setPublic(true);
        });

        $this->assertTrue($container->hasDefinition('jms_serializer.cache.cache_warmer'));

        $def = $container->getDefinition('jms_serializer.cache.cache_warmer');

        $this->assertEquals(['a'], $def->getArgument(0));
        $this->assertEquals(['b'], $def->getArgument(2));
    }

    public function testWarmUpWithDirsWithNoPaths()
    {
        $this->getContainerForConfig([[]], function ($container) {
            $this->assertFalse($container->hasDefinition('jms_serializer.cache.cache_warmer'));
        });
    }

    /**
     * @expectedException \JMS\Serializer\Exception\RuntimeException
     * @expectedExceptionMessage  The metadata directory "foo_dir" does not exist for the namespace "foo_ns"
     */
    public function testLoadNotExistentMetadataDir()
    {
        $this->getContainerForConfig(array(array(
            'metadata' => [
                'directories' => [
                    'foo' => [
                        'namespace_prefix' => 'foo_ns',
                        'path' => 'foo_dir',
                    ]
                ]
            ]
        )));
    }

    /**
     * @dataProvider getJsonVisitorConfigs
     */
    public function testJsonVisitorOptions($expectedOptions, $config)
    {
        $container = $this->getContainerForConfig(array($config), function ($container) {
            $container->getDefinition('jms_serializer.json_serialization_visitor')->setPublic(true);
        });
        $this->assertSame($expectedOptions, $container->get('jms_serializer.json_serialization_visitor')->getOptions());
    }

    public function getJsonVisitorConfigs()
    {
        $configs = array();

        if (version_compare(PHP_VERSION, '5.4', '>=')) {
            $configs[] = array(JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT, array(
                'visitors' => array(
                    'json' => array(
                        'options' => array('JSON_UNESCAPED_UNICODE', 'JSON_PRETTY_PRINT')
                    )
                )
            ));

            $configs[] = array(JSON_UNESCAPED_UNICODE, array(
                'visitors' => array(
                    'json' => array(
                        'options' => 'JSON_UNESCAPED_UNICODE'
                    )
                )
            ));
        }

        $configs[] = array(128, array(
            'visitors' => array(
                'json' => array(
                    'options' => 128
                )
            )
        ));

        $configs[] = array(0, array());

        return $configs;
    }

    public function testExpressionLanguage()
    {
        if (!interface_exists('Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface')) {
            $this->markTestSkipped("The Symfony Expression Language is not available");
        }
        $container = $this->getContainerForConfig(array(array()));
        $serializer = $container->get('jms_serializer');
        // test that all components have been wired correctly
        $object = new ObjectUsingExpressionLanguage('foo', true);
        $this->assertEquals('{"name":"foo"}', $serializer->serialize($object, 'json'));
        $object = new ObjectUsingExpressionLanguage('foo', false);
        $this->assertEquals('{}', $serializer->serialize($object, 'json'));
    }

    public function testExpressionLanguageVirtualProperties()
    {
        if (!interface_exists('Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface')) {
            $this->markTestSkipped("The Symfony Expression Language is not available");
        }
        $container = $this->getContainerForConfig(array(array()));
        $serializer = $container->get('jms_serializer');
        // test that all components have been wired correctly
        $object = new ObjectUsingExpressionProperties('foo');
        $this->assertEquals('{"v_prop_name":"foo"}', $serializer->serialize($object, 'json'));
    }

    /**
     * @expectedException \JMS\Serializer\Exception\ExpressionLanguageRequiredException
     */
    public function testExpressionLanguageDisabledVirtualProperties()
    {
        if (!interface_exists('Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface')) {
            $this->markTestSkipped("The Symfony Expression Language is not available");
        }
        $container = $this->getContainerForConfig(array(array('expression_evaluator' => array('id' => null))));
        $serializer = $container->get('jms_serializer');
        // test that all components have been wired correctly
        $object = new ObjectUsingExpressionProperties('foo');
        $serializer->serialize($object, 'json');
    }

    /**
     * @expectedException \JMS\Serializer\Exception\ExpressionLanguageRequiredException
     * @expectedExceptionMessage  To use conditional exclude/expose in JMS\SerializerBundle\Tests\DependencyInjection\Fixture\ObjectUsingExpressionLanguage you must configure the expression language.
     */
    public function testExpressionLanguageNotLoaded()
    {
        $container = $this->getContainerForConfig(array(array('expression_evaluator' => array('id' => null))));
        $serializer = $container->get('jms_serializer');
        // test that all components have been wired correctly
        $object = new ObjectUsingExpressionLanguage('foo', true);
        $serializer->serialize($object, 'json');
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage Invalid configuration for path "jms_serializer.expression_evaluator.id": You need at least symfony/expression language v2.6 or v3.0 to use the expression evaluator features
     */
    public function testExpressionInvalidEvaluator()
    {
        if (interface_exists('Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface')) {
            $this->markTestSkipped('To pass this test the "symfony/expression-language" component should be available');
        }
        $this->getContainerForConfig(array(array('expression_evaluator' => array('id' => 'foo'))));
    }

    /**
     * @dataProvider getXmlVisitorWhitelists
     */
    public function testXmlVisitorOptions($expectedOptions, $config)
    {
        $container = $this->getContainerForConfig(array($config), function ($container) {
            $container->getDefinition('jms_serializer.xml_deserialization_visitor')->setPublic(true);
        });
        $this->assertSame($expectedOptions, $container->get('jms_serializer.xml_deserialization_visitor')->getDoctypeWhitelist());
    }

    public function getXmlVisitorWhitelists()
    {
        $configs = array();

        $configs[] = array(array('good document', 'other good document'), array(
            'visitors' => array(
                'xml' => array(
                    'doctype_whitelist' => array('good document', 'other good document'),
                )
            )
        ));

        $configs[] = array(array(), array());

        return $configs;
    }

    public function testXmlVisitorFormatOutput()
    {
        $config = array(
            'visitors' => array(
                'xml' => array(
                    'format_output' => false,
                )
            )
        );
        $container = $this->getContainerForConfig(array($config), function ($container) {
            $container->getDefinition('jms_serializer.xml_serialization_visitor')->setPublic(true);
        });

        $this->assertFalse($container->get('jms_serializer.xml_serialization_visitor')->isFormatOutput());
    }

    public function testXmlVisitorDefaultValueToFormatOutput()
    {
        $container = $this->getContainerForConfig(array(), function ($container) {
            $container->getDefinition('jms_serializer.xml_serialization_visitor')->setPublic(true);
        });
        $this->assertTrue($container->get('jms_serializer.xml_serialization_visitor')->isFormatOutput());
    }

    private function getContainerForConfig(array $configs, callable $configurator = null)
    {
        $bundle = new JMSSerializerBundle();
        $extension = $bundle->getContainerExtension();

        $container = new ContainerBuilder();
        $container->setParameter('kernel.debug', true);
        $container->setParameter('kernel.cache_dir', sys_get_temp_dir() . '/serializer');
        $container->setParameter('kernel.bundles', array());
        $container->set('annotation_reader', new AnnotationReader());
        $container->setDefinition('doctrine', new Definition(Registry::class));
        $container->set('translator', $this->getMockBuilder('Symfony\\Component\\Translation\\TranslatorInterface')->getMock());
        $container->set('debug.stopwatch', $this->getMockBuilder('Symfony\\Component\\Stopwatch\\Stopwatch')->getMock());
        $container->registerExtension($extension);
        $extension->load($configs, $container);

        $bundle->build($container);

        if ($configurator) {
            call_user_func($configurator, $container);
        }

        $container->compile();

        return $container;
    }
}

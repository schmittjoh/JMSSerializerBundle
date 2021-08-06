<?php

declare(strict_types=1);

namespace JMS\SerializerBundle\Tests\DependencyInjection;

use Doctrine\Common\Annotations\AnnotationReader;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\Exception\ExpressionLanguageRequiredException;
use JMS\Serializer\Exception\RuntimeException;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\Metadata\Driver\TypedPropertiesDriver;
use JMS\Serializer\SerializationContext;
use JMS\SerializerBundle\JMSSerializerBundle;
use JMS\SerializerBundle\Tests\DependencyInjection\Fixture\IncludeInterfaces\AnInterfaceImplementation;
use JMS\SerializerBundle\Tests\DependencyInjection\Fixture\IncludeInterfaces\AnObject;
use JMS\SerializerBundle\Tests\DependencyInjection\Fixture\ObjectUsingExpressionLanguage;
use JMS\SerializerBundle\Tests\DependencyInjection\Fixture\ObjectUsingExpressionProperties;
use JMS\SerializerBundle\Tests\DependencyInjection\Fixture\SimpleObject;
use JMS\SerializerBundle\Tests\DependencyInjection\Fixture\TypedObject;
use JMS\SerializerBundle\Tests\DependencyInjection\Fixture\VersionedObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class JMSSerializerExtensionTest extends TestCase
{
    protected function setUp(): void
    {
        $this->clearTempDir();
    }

    protected function tearDown(): void
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
        $container = $this->getContainerForConfig([[]]);

        $factory = $container->get('jms_serializer.serialization_context_factory');
        $this->assertInstanceOf('JMS\Serializer\ContextFactory\SerializationContextFactoryInterface', $factory);

        $factory = $container->get('jms_serializer.deserialization_context_factory');
        $this->assertInstanceOf('JMS\Serializer\ContextFactory\DeserializationContextFactoryInterface', $factory);
    }

    public function testSerializerContextFactoriesAreSet()
    {
        $container = $this->getContainerForConfig([[]]);

        $def = $container->getDefinition('jms_serializer');
        $serializationFactoryArg = $def->getArgument(4);
        $deSerializationFactoryArg = $def->getArgument(5);

        $this->assertEquals('jms_serializer.serialization_context_factory', (string) $serializationFactoryArg);
        $this->assertEquals('jms_serializer.deserialization_context_factory', (string) $deSerializationFactoryArg);
    }

    public function testSerializerContextFactoriesWithId()
    {
        $config = [
            'default_context' => [
                'serialization' => ['id' => 'foo'],
                'deserialization' => ['id' => 'bar'],
            ],
        ];

        $foo = new Definition('stdClass');
        $foo->setPublic(true);
        $bar = new Definition('stdClass');
        $bar->setPublic(true);

        $container = $this->getContainerForConfig([$config], static function (ContainerBuilder $containerBuilder) use ($foo, $bar) {
            $containerBuilder->setDefinition('foo', $foo);
            $containerBuilder->setDefinition('bar', $bar);
        });

        $def = $container->getDefinition('jms_serializer');

        $serializationFactoryArg = $def->getArgument(4);
        $deSerializationFactoryArg = $def->getArgument(5);

        $this->assertEquals('foo', (string) $serializationFactoryArg);
        $this->assertEquals('bar', (string) $deSerializationFactoryArg);

        $serializationContextFactoryAlias = $container->getAlias('JMS\Serializer\ContextFactory\SerializationContextFactoryInterface');
        $deserializationContextFactoryAlias = $container->getAlias('JMS\Serializer\ContextFactory\DeserializationContextFactoryInterface');

        $this->assertEquals('foo', (string) $serializationContextFactoryAlias);
        $this->assertEquals('bar', (string) $deserializationContextFactoryAlias);
    }

    public function testLoadWithoutTranslator()
    {
        $container = $this->getContainerForConfig([[]], static function (ContainerBuilder $containerBuilder) {
            $containerBuilder->set('translator', null);
            $containerBuilder->getDefinition('jms_serializer.form_error_handler')->setPublic(true);
        });

        $def = $container->getDefinition('jms_serializer.form_error_handler');
        $this->assertSame(null, $def->getArgument(0));
    }

    public function testConfiguringContextFactories()
    {
        $container = $this->getContainerForConfig([[]]);

        $def = $container->getDefinition('jms_serializer.serialization_context_factory');
        $this->assertCount(0, $def->getMethodCalls());

        $def = $container->getDefinition('jms_serializer.deserialization_context_factory');
        $this->assertCount(0, $def->getMethodCalls());
    }

    public function testConfiguringContextFactoriesWithParams()
    {
        $config = [
            'default_context' => [
                'serialization' => [
                    'version' => 1600,
                    'serialize_null' => true,
                    'attributes' => ['x' => 1720],
                    'groups' => ['Default', 'Registration'],
                    'enable_max_depth_checks' => true,
                ],
                'deserialization' => [
                    'version' => 1640,
                    'serialize_null' => false,
                    'attributes' => ['x' => 1740],
                    'groups' => ['Default', 'Profile'],
                    'enable_max_depth_checks' => true,
                ],
            ],
        ];

        $container = $this->getContainerForConfig([$config]);
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
            $this->assertSame([], $this->getDefinitionMethodCall($def, 'enableMaxDepthChecks'));
        }
    }

    public function testConfiguringContextFactoriesWithNullDefaults()
    {
        $config = [
            'default_context' => [
                'serialization' => [
                    'version' => null,
                    'serialize_null' => null,
                    'attributes' => [],
                    'groups' => null,
                ],
                'deserialization' => [
                    'version' => null,
                    'serialize_null' => null,
                    'attributes' => null,
                    'groups' => null,
                ],
            ],
        ];

        $container = $this->getContainerForConfig([$config]);
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
        $container = $this->getContainerForConfig([[]], static function (ContainerBuilder $container) {
            $container->getDefinition('jms_serializer.doctrine_object_constructor')->setPublic(true);
            $container->getDefinition('jms_serializer.array_collection_handler')->setPublic(true);
            $container->getDefinition('jms_serializer.doctrine_proxy_subscriber')->setPublic(true);
            $container->getAlias('JMS\Serializer\SerializerInterface')->setPublic(true);
            $container->getAlias('JMS\Serializer\ArrayTransformerInterface')->setPublic(true);
            $container->getAlias('JMS\Serializer\ContextFactory\SerializationContextFactoryInterface')->setPublic(true);
            $container->getAlias('JMS\Serializer\ContextFactory\DeserializationContextFactoryInterface')->setPublic(true);
        });

        $simpleObject = new SimpleObject('foo', 'bar');
        $versionedObject = new VersionedObject('foo', 'bar');
        $serializer = $container->get('jms_serializer');

        $this->assertTrue($container->has('JMS\Serializer\SerializerInterface'), 'Alias should be defined to allow autowiring');
        $this->assertTrue($container->has('JMS\Serializer\ArrayTransformerInterface'), 'Alias should be defined to allow autowiring');
        $this->assertTrue($container->has('JMS\Serializer\ContextFactory\SerializationContextFactoryInterface'), 'Alias should be defined to allow autowiring');
        $this->assertTrue($container->has('JMS\Serializer\ContextFactory\DeserializationContextFactoryInterface'), 'Alias should be defined to allow autowiring');

        $this->assertFalse($container->getDefinition('jms_serializer.array_collection_handler')->getArgument(0));

        // the logic is inverted because arg 0 on doctrine_proxy_subscriber is $skipVirtualTypeInit = false
        $this->assertTrue($container->getDefinition('jms_serializer.doctrine_proxy_subscriber')->getArgument(0));
        $this->assertFalse($container->getDefinition('jms_serializer.doctrine_proxy_subscriber')->getArgument(1));

        $this->assertEquals('null', $container->getDefinition('jms_serializer.doctrine_object_constructor')->getArgument(2));

        // test that all components have been wired correctly
        $this->assertEquals(json_encode(['name' => 'bar']), $serializer->serialize($versionedObject, 'json'));
        $this->assertEquals($simpleObject, $serializer->deserialize($serializer->serialize($simpleObject, 'json'), get_class($simpleObject), 'json'));
        $this->assertEquals($simpleObject, $serializer->deserialize($serializer->serialize($simpleObject, 'xml'), get_class($simpleObject), 'xml'));

        $this->assertEquals(json_encode(['name' => 'foo']), $serializer->serialize($versionedObject, 'json', SerializationContext::create()->setVersion('0.0.1')));

        $this->assertEquals(json_encode(['name' => 'bar']), $serializer->serialize($versionedObject, 'json', SerializationContext::create()->setVersion('1.1.1')));

        $this->assertEquals(json_encode(['name' => 'foo']), $serializer->serialize($versionedObject, 'json', $container->get('JMS\Serializer\ContextFactory\SerializationContextFactoryInterface')->createSerializationContext()->setVersion('0.0.1')));
    }

    public function testLoadWithOptions()
    {
        $container = $this->getContainerForConfig([
            [
                'subscribers' => [
                    'doctrine_proxy' => [
                        'initialize_virtual_types' => true,
                        'initialize_excluded' => true,
                    ],
                ],
                'object_constructors' => [
                    'doctrine' => ['fallback_strategy' => 'exception'],
                ],
                'handlers' => [
                    'array_collection' => ['initialize_excluded' => true],
                ],
            ],
        ], static function ($container) {
                $container->getDefinition('jms_serializer.doctrine_object_constructor')->setPublic(true);
                $container->getDefinition('jms_serializer.array_collection_handler')->setPublic(true);
                $container->getDefinition('jms_serializer.doctrine_proxy_subscriber')->setPublic(true);
        });

        $this->assertTrue($container->getDefinition('jms_serializer.array_collection_handler')->getArgument(0));

        // the logic is inverted because arg 0 on doctrine_proxy_subscriber is $skipVirtualTypeInit = false
        $this->assertFalse($container->getDefinition('jms_serializer.doctrine_proxy_subscriber')->getArgument(0));
        $this->assertTrue($container->getDefinition('jms_serializer.doctrine_proxy_subscriber')->getArgument(1));

        $this->assertEquals('exception', $container->getDefinition('jms_serializer.doctrine_object_constructor')->getArgument(2));
    }

    public function testLoadExistentMetadataDir()
    {
        $container = $this->getContainerForConfig([
            [
                'metadata' => [
                    'directories' => [
                        'foo' => [
                            'namespace_prefix' => 'foo_ns',
                            'path' => __DIR__,
                        ],
                    ],
                ],
            ],
        ], static function ($container) {
                $container->getDefinition('jms_serializer.metadata.file_locator')->setPublic(true);
        });

        $fileLocatorDef = $container->getDefinition('jms_serializer.metadata.file_locator');
        $directories = $fileLocatorDef->getArgument(0);
        $this->assertEquals(['foo_ns' => __DIR__], $directories);
    }

    public function testWarmUpWithDirs()
    {
        $container = $this->getContainerForConfig([
            [
                'metadata' => [
                    'warmup' => [
                        'paths' => [
                            'included' => ['a'],
                            'excluded' => ['b'],
                        ],
                    ],
                ],
            ],
        ], static function ($container) {
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

    public function testLoadNotExistentMetadataDir()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The metadata directory "foo_dir" does not exist for the namespace "foo_ns"');

        $this->getContainerForConfig([
            [
                'metadata' => [
                    'directories' => [
                        'foo' => [
                            'namespace_prefix' => 'foo_ns',
                            'path' => 'foo_dir',
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * @dataProvider getJsonVisitorConfigs
     */
    public function testJsonVisitorOptions($expectedOptions, $config)
    {
        $container = $this->getContainerForConfigLoad([$config]);
        $visitor = $container->getDefinition('jms_serializer.json_serialization_visitor');

        $calls = $visitor->getMethodCalls();

        $this->assertEquals('setOptions', $calls[0][0]);
        $this->assertEquals($expectedOptions, $calls[0][1][0]);
    }

    public function getJsonVisitorConfigs()
    {
        $configs = [];

        $configs[] = [
            JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT,
            [
                'visitors' => [
                    'json_serialization' => [
                        'options' => ['JSON_UNESCAPED_UNICODE', 'JSON_PRETTY_PRINT'],
                    ],
                ],
            ],
        ];

        $configs[] = [
            JSON_UNESCAPED_UNICODE,
            [
                'visitors' => [
                    'json_serialization' => ['options' => 'JSON_UNESCAPED_UNICODE'],
                ],
            ],
        ];

        $configs[] = [
            128,
            [
                'visitors' => [
                    'json_serialization' => ['options' => 128],
                ],
            ],
        ];

        $configs[] = [
            0,
            [
                'visitors' => [
                    'json_serialization' => [
                        'options' => [],
                    ],
                ],
            ],
        ];

        $configs[] = [
            JSON_PRESERVE_ZERO_FRACTION,
            [
                'visitors' => [
                    'json_serialization' => ['options' => 'JSON_PRESERVE_ZERO_FRACTION'],
                ],
            ],
        ];

        return $configs;
    }

    /**
     * @dataProvider getJsonVisitorOptions
     */
    public function testPassJsonVisitorOptions(string $expected, $data, $options)
    {
        $container = $this->getContainerForConfig([
            [
                'visitors' => [
                    'json_serialization' => ['options' => $options],
                ],
            ],
        ]);
        $serializer = $container->get('jms_serializer');

        $this->assertSame($expected, $serializer->serialize($data, 'json'));
    }

    public function getJsonVisitorOptions()
    {
        return [
            ['0', 0.0, 0],
            ['0', 0.0, []],
            ['0.0', 0.0, 'JSON_PRESERVE_ZERO_FRACTION'],
        ];
    }

    public function testEmptyJsonVisitorOptions()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Expected either integer value or a array of the JSON_ constants.');

        $this->getContainerForConfig([
            [
                'visitors' => [
                    'json_serialization' => ['options' => null],
                ],
            ],
        ]);
    }

    public function testExpressionLanguage()
    {
        if (!interface_exists('Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface')) {
            $this->markTestSkipped('The Symfony Expression Language is not available');
        }

        $container = $this->getContainerForConfig([[]]);
        $serializer = $container->get('jms_serializer');
        // test that all components have been wired correctly
        $object = new ObjectUsingExpressionLanguage('foo', true);
        $this->assertEquals('{"virtual":"bar","name":"foo"}', $serializer->serialize($object, 'json'));
        $object = new ObjectUsingExpressionLanguage('foo', false);
        $this->assertEquals('{"virtual":"bar"}', $serializer->serialize($object, 'json'));
    }

    public function testExpressionLanguageVirtualProperties()
    {
        if (!interface_exists('Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface')) {
            $this->markTestSkipped('The Symfony Expression Language is not available');
        }

        $container = $this->getContainerForConfig([[]]);
        $serializer = $container->get('jms_serializer');
        // test that all components have been wired correctly
        $object = new ObjectUsingExpressionProperties('foo');
        $this->assertEquals('{"v_prop_name":"foo"}', $serializer->serialize($object, 'json'));
    }

    public function testExpressionLanguageDisabledVirtualProperties()
    {
        $this->expectException(ExpressionLanguageRequiredException::class);

        if (!interface_exists('Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface')) {
            $this->markTestSkipped('The Symfony Expression Language is not available');
        }

        $container = $this->getContainerForConfig([['expression_evaluator' => ['id' => null]]]);
        $serializer = $container->get('jms_serializer');
        // test that all components have been wired correctly
        $object = new ObjectUsingExpressionProperties('foo');
        $serializer->serialize($object, 'json');
    }

    public function testExpressionLanguageNotLoaded()
    {
        $this->expectException(ExpressionLanguageRequiredException::class);
        $this->expectExceptionMessage('To use conditional exclude/expose in JMS\SerializerBundle\Tests\DependencyInjection\Fixture\ObjectUsingExpressionLanguage you must configure the expression language.');

        $container = $this->getContainerForConfig([['expression_evaluator' => ['id' => null]]]);
        $serializer = $container->get('jms_serializer');
        // test that all components have been wired correctly
        $object = new ObjectUsingExpressionLanguage('foo', true);
        $serializer->serialize($object, 'json');
    }

    public function testExpressionInvalidEvaluator()
    {
        $this->expectExceptionMessage('Invalid configuration for path "jms_serializer.expression_evaluator.id": You need at least symfony/expression-language v2.6 or v3.0 to use the expression evaluator features');
        $this->expectException(InvalidConfigurationException::class);

        if (interface_exists('Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface')) {
            $this->markTestSkipped('To pass this test the "symfony/expression-language" component should be available');
        }

        $this->getContainerForConfig([['expression_evaluator' => ['id' => 'foo']]]);
    }

    /**
     * @dataProvider getXmlVisitorWhitelists
     */
    public function testXmlVisitorDoctypeWhitelist($expectedOptions, $config)
    {
        $container = $this->getContainerForConfigLoad([$config]);
        $visitor = $container->getDefinition('jms_serializer.xml_deserialization_visitor');

        $calls = $visitor->getMethodCalls();

        $this->assertEquals('setDoctypeWhitelist', $calls[0][0]);
        $this->assertEquals($expectedOptions, $calls[0][1][0]);
    }

    public function getXmlVisitorWhitelists()
    {
        $configs = [];

        $configs[] = [
            ['good document', 'other good document'],
            [
                'visitors' => [
                    'xml_deserialization' => [
                        'doctype_whitelist' => ['good document', 'other good document'],
                    ],
                ],
            ],
        ];

        return $configs;
    }

    public function testXmlDeserializationVisitorOptions()
    {
        $container = $this->getContainerForConfigLoad([
            [
                'visitors' => [
                    'xml_deserialization' => [
                        'options' => LIBXML_BIGLINES | LIBXML_NOBLANKS,
                    ],
                ],
            ],
        ]);
        $visitor = $container->getDefinition('jms_serializer.xml_deserialization_visitor');

        $calls = $visitor->getMethodCalls();

        $this->assertEquals('setOptions', $calls[0][0]);
        $this->assertEquals(LIBXML_BIGLINES | LIBXML_NOBLANKS, $calls[0][1][0]);
    }

    public function testXmlVisitorFormatOutput()
    {
        $config = [
            'visitors' => [
                'xml_serialization' => ['format_output' => true],
            ],
        ];
        $container = $this->getContainerForConfigLoad([$config]);
        $visitor = $container->getDefinition('jms_serializer.xml_serialization_visitor');

        $calls = $visitor->getMethodCalls();

        $this->assertEquals('setFormatOutput', $calls[0][0]);
        $this->assertEquals(true, $calls[0][1][0]);
    }

    public function testAutoconfigureSubscribers()
    {
        $container = $this->getContainerForConfig([]);

        if (!method_exists($container, 'registerForAutoconfiguration')) {
            $this->markTestSkipped(
                'registerForAutoconfiguration method is not available in the container'
            );
        }

        $autoconfigureInstance = $container->getAutoconfiguredInstanceof();

        $this->assertTrue(array_key_exists(EventSubscriberInterface::class, $autoconfigureInstance));
        $this->assertTrue($autoconfigureInstance[EventSubscriberInterface::class]->hasTag('jms_serializer.event_subscriber'));
    }

    public function testAutoconfigureHandlers()
    {
        $container = $this->getContainerForConfig([]);
        $autoconfigureInstance = $container->getAutoconfiguredInstanceof();

        $this->assertTrue(array_key_exists(SubscribingHandlerInterface::class, $autoconfigureInstance));
        $this->assertTrue($autoconfigureInstance[SubscribingHandlerInterface::class]->hasTag('jms_serializer.subscribing_handler'));
    }

    public function testTypedDriverIsEnabled()
    {
        if (PHP_VERSION_ID < 70400) {
            $this->markTestSkipped(sprintf('%s requires PHP 7.4', __METHOD__));
        }

        if (!class_exists(TypedPropertiesDriver::class)) {
            $this->markTestSkipped(sprintf('%s requires %s', __METHOD__, TypedPropertiesDriver::class));
        }

        $container = $this->getContainerForConfig([[]]);

        $metadata = $container->get('jms_serializer.metadata_driver')
            ->loadMetadataForClass(new \ReflectionClass(TypedObject::class));

        self::assertSame('int', $metadata->propertyMetadata['foo']->type['name']);
    }

    public function testIncludeInterfaces()
    {
        $container = $this->getContainerForConfig([
            [
                'metadata' => ['include_interfaces' => true],
            ],
        ]);
        $serializer = $container->get('jms_serializer');

        $actual = $serializer->toArray(
            new AnObject(
                'foo',
                new AnInterfaceImplementation(
                    'bar'
                )
            )
        );
        $expected = [
            'foo' => 'foo',
            'bar' => [
                'bar' => 'bar',
                'type' => 'a',
            ],
        ];

        self::assertSame($expected, $actual);
    }

    private function getContainerForConfigLoad(array $configs, ?callable $configurator = null)
    {
        $bundle = new JMSSerializerBundle();
        $extension = $bundle->getContainerExtension();

        $container = new ContainerBuilder();
        $container->setParameter('kernel.debug', true);
        $container->setParameter('kernel.cache_dir', sys_get_temp_dir() . '/serializer');
        $container->setParameter('kernel.bundles', []);
        $container->setParameter('foo', 'bar');
        $container->set('annotation_reader', new AnnotationReader());
        $container->setDefinition('doctrine', new Definition(Registry::class));
        $container->set('translator', $this->getMockBuilder('Symfony\\Component\\Translation\\TranslatorInterface')->getMock());
        $container->set('debug.stopwatch', $this->getMockBuilder('Symfony\\Component\\Stopwatch\\Stopwatch')->getMock());
        $container->registerExtension($extension);
        $extension->load($configs, $container);

        $bundle->build($container);

        return $container;
    }

    private function getContainerForConfig(array $configs, ?callable $configurator = null)
    {
        $container = $this->getContainerForConfigLoad($configs);
        if ($configurator) {
            call_user_func($configurator, $container);
        }

        $container->compile();

        return $container;
    }
}

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

use JMS\Serializer\SerializationContext;
use JMS\SerializerBundle\Tests\DependencyInjection\Fixture\ObjectUsingExpressionLanguage;
use JMS\SerializerBundle\Tests\DependencyInjection\Fixture\ObjectUsingExpressionProperties;
use Doctrine\Common\Annotations\AnnotationReader;
use JMS\SerializerBundle\JMSSerializerBundle;
use JMS\SerializerBundle\Tests\DependencyInjection\Fixture\SimpleObject;
use JMS\SerializerBundle\Tests\DependencyInjection\Fixture\VersionedObject;
use Symfony\Component\DependencyInjection\Compiler\ResolveDefinitionTemplatesPass;
use Symfony\Component\DependencyInjection\Compiler\ResolveParameterPlaceHoldersPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\KernelInterface;

class JMSSerializerExtensionTest extends \PHPUnit_Framework_TestCase
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
        $dir = sys_get_temp_dir().'/serializer';
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

        $factory = $container->get('jms_serializer.configured_serialization_context_factory');
        $this->assertInstanceOf('JMS\Serializer\ContextFactory\SerializationContextFactoryInterface', $factory);

        $factory = $container->get('jms_serializer.configured_deserialization_context_factory');
        $this->assertInstanceOf('JMS\Serializer\ContextFactory\DeserializationContextFactoryInterface', $factory);
    }

    public function testSerializerContextFactoriesAreSet()
    {
        $container = $this->getContainerForConfig(array(array()));

        $def = $container->getDefinition('jms_serializer.serializer');
        $calls = $def->getMethodCalls();

        $this->assertCount(2, $calls);

        $serializationCall = $calls[0];
        $this->assertEquals('setSerializationContextFactory', $serializationCall[0]);
        $this->assertEquals('jms_serializer.serialization_context_factory', (string)$serializationCall[1][0]);

        $serializationCall = $calls[1];
        $this->assertEquals('setDeserializationContextFactory', $serializationCall[0]);
        $this->assertEquals('jms_serializer.deserialization_context_factory', (string)$serializationCall[1][0]);

        $this->assertEquals('jms_serializer.configured_deserialization_context_factory', (string)$container->getAlias('jms_serializer.deserialization_context_factory'));
        $this->assertEquals('jms_serializer.configured_serialization_context_factory', (string)$container->getAlias('jms_serializer.serialization_context_factory'));
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

        $container = $this->getContainerForConfig(array($config));

        $def = $container->getDefinition('jms_serializer.serializer');
        $calls = $def->getMethodCalls();

        $this->assertCount(2, $calls);

        $serializationCall = $calls[0];
        $this->assertEquals('setSerializationContextFactory', $serializationCall[0]);
        $this->assertEquals('jms_serializer.serialization_context_factory', (string)$serializationCall[1][0]);

        $serializationCall = $calls[1];
        $this->assertEquals('setDeserializationContextFactory', $serializationCall[0]);
        $this->assertEquals('jms_serializer.deserialization_context_factory', (string)$serializationCall[1][0]);

        $this->assertEquals('bar', (string)$container->getAlias('jms_serializer.deserialization_context_factory'));
        $this->assertEquals('foo', (string)$container->getAlias('jms_serializer.serialization_context_factory'));
    }

    public function testConfiguringContextFactories()
    {
        $container = $this->getContainerForConfig(array(array()));

        $def = $container->getDefinition('jms_serializer.configured_serialization_context_factory');
        $this->assertCount(0, $def->getMethodCalls());

        $def = $container->getDefinition('jms_serializer.configured_deserialization_context_factory');
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
                    'groups' => array('Default', 'Registration')
                ),
                'deserialization' => array(
                    'version' => 1640,
                    'serialize_null' => false,
                    'attributes' => array('x' => 1740),
                    'groups' => array('Default', 'Profile')
                )
            )
        );

        $container = $this->getContainerForConfig(array($config));
        $services  = [
            'serialization' => 'jms_serializer.configured_serialization_context_factory',
            'deserialization' => 'jms_serializer.configured_deserialization_context_factory',
        ];
        foreach ($services as $configKey => $serviceId) {
            $def    = $container->getDefinition($serviceId);
            $values = $config['default_context'][$configKey];

            $this->assertEquals($values['version'], $this->getDefinitionMethodCall($def, 'setVersion')[0]);
            $this->assertEquals($values['serialize_null'], $this->getDefinitionMethodCall($def, 'setSerializeNulls')[0]);
            $this->assertEquals($values['attributes'], $this->getDefinitionMethodCall($def, 'setAttributes')[0]);
            $this->assertEquals($values['groups'], $this->getDefinitionMethodCall($def, 'setGroups')[0]);
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
            'serialization' => 'jms_serializer.configured_serialization_context_factory',
            'deserialization' => 'jms_serializer.configured_deserialization_context_factory',
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
        $container = $this->getContainerForConfig(array(array()));

        $simpleObject = new SimpleObject('foo', 'bar');
        $versionedObject  = new VersionedObject('foo', 'bar');
        $serializer = $container->get('serializer');

        // test that all components have been wired correctly
        $this->assertEquals(json_encode(array('name' => 'bar')), $serializer->serialize($versionedObject, 'json'));
        $this->assertEquals($simpleObject, $serializer->deserialize($serializer->serialize($simpleObject, 'json'), get_class($simpleObject), 'json'));
        $this->assertEquals($simpleObject, $serializer->deserialize($serializer->serialize($simpleObject, 'xml'), get_class($simpleObject), 'xml'));

        $this->assertEquals(json_encode(array('name' => 'foo')), $serializer->serialize($versionedObject, 'json', SerializationContext::create()->setVersion('0.0.1')));

        $this->assertEquals(json_encode(array('name' => 'bar')), $serializer->serialize($versionedObject, 'json', SerializationContext::create()->setVersion('1.1.1')));
    }

    /**
     * @dataProvider getJsonVisitorConfigs
     */
    public function testJsonVisitorOptions($expectedOptions, $config)
    {
        $container = $this->getContainerForConfig(array($config));
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
        $serializer = $container->get('serializer');
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
        $serializer = $container->get('serializer');
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
        $serializer = $container->get('serializer');
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
        $serializer = $container->get('serializer');
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
        $container = $this->getContainerForConfig(array($config));
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
        $container = $this->getContainerForConfig(array($config));

        $this->assertFalse($container->get('jms_serializer.xml_serialization_visitor')->isFormatOutput());
    }

    public function testXmlVisitorDefaultValueToFormatOutput()
    {
        $container = $this->getContainerForConfig(array());
        $this->assertTrue($container->get('jms_serializer.xml_serialization_visitor')->isFormatOutput());
    }

    private function getContainerForConfig(array $configs, KernelInterface $kernel = null)
    {
        if (null === $kernel) {
            $kernel = $this->getMockBuilder('Symfony\Component\HttpKernel\KernelInterface')->getMock();
            $kernel
                ->expects($this->any())
                ->method('getBundles')
                ->will($this->returnValue(array()))
            ;
        }

        $bundle = new JMSSerializerBundle($kernel);
        $extension = $bundle->getContainerExtension();

        $container = new ContainerBuilder();
        $container->setParameter('kernel.debug', true);
        $container->setParameter('kernel.cache_dir', sys_get_temp_dir().'/serializer');
        $container->setParameter('kernel.bundles', array());
        $container->set('annotation_reader', new AnnotationReader());
        $container->set('translator', $this->getMockBuilder('Symfony\\Component\\Translation\\TranslatorInterface')->getMock());
        $container->set('debug.stopwatch', $this->getMockBuilder('Symfony\\Component\\Stopwatch\\Stopwatch')->getMock());
        $container->registerExtension($extension);
        $extension->load($configs, $container);

        $bundle->build($container);

        $container->getCompilerPassConfig()->setOptimizationPasses(array(
            new ResolveParameterPlaceHoldersPass(),
            new ResolveDefinitionTemplatesPass(),
        ));
        $container->getCompilerPassConfig()->setRemovingPasses(array());
        $container->compile();

        return $container;
    }
}

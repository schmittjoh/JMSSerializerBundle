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
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
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
                        [
                            'namespace_prefix' => 'JMSSerializerBundleNs1',
                            'path' => '@JMSSerializerBundle',
                        ],
                        [
                            'namespace_prefix' => 'JMSSerializerBundleNs2',
                            'path' => '@JMSSerializerBundle/Resources/config',
                        ],
                    ]
                ]
            ],
        ]);

        $directories = $container->getDefinition('jms_serializer.metadata.file_locator')->getArgument(0);

        $this->assertEquals($ref->getPath(), $directories['JMSSerializerBundleNs1']);
        $this->assertEquals($ref->getPath().'/Resources/config', $directories['JMSSerializerBundleNs2']);
    }

    public function testContextDefaults()
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(true), []);

        $this->assertArrayHasKey('context', $config);
        $this->assertArrayHasKey('attributes', $config['context']);
        $this->assertTrue(is_array($config['context']['attributes']));
        $this->assertEmpty($config['context']['attributes']);
        $this->assertArrayHasKey('groups', $config['context']);
        $this->assertSame(['Default'], $config['context']['groups']);
        $this->assertArrayHasKey('version', $config['context']);
        $this->assertNull($config['context']['version']);
        $this->assertArrayHasKey('serialize_null', $config['context']);
        $this->assertFalse($config['context']['serialize_null']);
    }
}

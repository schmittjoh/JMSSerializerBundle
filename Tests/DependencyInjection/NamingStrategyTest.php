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

use JMS\Serializer\Metadata\PropertyMetadata;
use JMS\Serializer\Naming\PropertyNamingStrategyInterface;
use JMS\SerializerBundle\DependencyInjection\JMSSerializerExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class NamingStrategyTest extends TestCase
{
    /**
     *
     * @param array $configs
     * @return ContainerBuilder
     */
    private function getContainer(array $configs = array())
    {
        $loader = new JMSSerializerExtension();
        $container = new ContainerBuilder();

        $container->setParameter('kernel.debug', true);
        $container->setParameter('kernel.cache_dir', sys_get_temp_dir() . '/serializer');
        $container->setParameter('kernel.bundles', array());

        $loader->load(['jms_serializer' => $configs], $container);
        return $container;
    }

    public function testCustomNamingStrategy()
    {
        $container = $this->getContainer(array(
            'property_naming' => array(
                'id' => 'custom_naming_strategy',
                'enable_cache' => false
            )
        ));
        $customNamingStrategy = new CustomNamingStrategy();
        $container->set("custom_naming_strategy", $customNamingStrategy);

        $this->assertSame($customNamingStrategy, $container->get('jms_serializer.naming_strategy'));
    }

    public function testCachedNamingStrategy()
    {
        $container = $this->getContainer(array(
            'property_naming' => array(
                'enable_cache' => true
            )
        ));

        $namingStrategy = $container->get('jms_serializer.naming_strategy');
        $this->assertInstanceOf('JMS\Serializer\Naming\CacheNamingStrategy', $namingStrategy);
    }
}

class CustomNamingStrategy implements PropertyNamingStrategyInterface
{
    public function translateName(PropertyMetadata $property)
    {
        return 'foo';
    }
}

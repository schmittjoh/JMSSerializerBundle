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

use JMS\SerializerBundle\DependencyInjection\Compiler\DoctrinePass;
use JMS\SerializerBundle\DependencyInjection\JMSSerializerExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DoctrinePassTest extends TestCase
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

    public function testDoctrineDisabled()
    {
        $container = $this->getContainer(array(
            'metadata' => array('infer_types_from_doctrine_metadata' => false)
        ));
        $container->register('doctrine.orm.entity_manager');

        $pass = new DoctrinePass();
        $pass->process($container);

        $alias = $container->getAlias('jms_serializer.object_constructor');
        $this->assertTrue($alias->isPublic());

        $this->assertEquals('jms_serializer.unserialize_object_constructor', (string)$alias);
    }

    public function testOrm()
    {
        $container = $this->getContainer();
        $container->register('doctrine.orm.entity_manager');

        $pass = new DoctrinePass();
        $pass->process($container);

        $alias = $container->getAlias('jms_serializer.object_constructor');
        $this->assertTrue($alias->isPublic());

        $this->assertEquals('jms_serializer.doctrine_object_constructor', (string)$alias);
    }

    public function testOdm()
    {
        $container = $this->getContainer();
        $container->register('doctrine_phpcr.odm.document_manager');

        $pass = new DoctrinePass();
        $pass->process($container);

        $alias = $container->getAlias('jms_serializer.object_constructor');
        $this->assertTrue($alias->isPublic());

        $this->assertEquals('jms_serializer.doctrine_phpcr_object_constructor', (string)$alias);

        $def = $container->getDefinition('jms_serializer.doctrine_phpcr_object_constructor');
        $this->assertEquals('jms_serializer.unserialize_object_constructor', (string)$def->getArgument(1));
    }

    public function testOrmAndOdm()
    {
        $container = $this->getContainer();

        $container->register('doctrine_phpcr.odm.document_manager');
        $container->register('doctrine.orm.entity_manager');

        $pass = new DoctrinePass();
        $pass->process($container);


        $alias = $container->getAlias('jms_serializer.object_constructor');
        $this->assertTrue($alias->isPublic());

        $this->assertEquals('jms_serializer.doctrine_object_constructor', (string)$alias);

        $def = $container->getDefinition('jms_serializer.doctrine_object_constructor');
        $this->assertEquals('jms_serializer.doctrine_phpcr_object_constructor', (string)$def->getArgument(1));

        $def = $container->getDefinition('jms_serializer.doctrine_phpcr_object_constructor');
        $this->assertEquals('jms_serializer.unserialize_object_constructor', (string)$def->getArgument(1));
    }
}


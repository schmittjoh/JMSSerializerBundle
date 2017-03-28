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

use JMS\Serializer\Exception\RuntimeException;
use JMS\SerializerBundle\DependencyInjection\Compiler\CustomHandlersPass;
use JMS\SerializerBundle\DependencyInjection\JMSSerializerExtension;
use Symfony\Component\DependencyInjection\Compiler\RemoveUnusedDefinitionsPass;
use Symfony\Component\DependencyInjection\Compiler\ResolveDefinitionTemplatesPass;
use Symfony\Component\DependencyInjection\Compiler\ResolveParameterPlaceHoldersPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class CustomHandlerPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param array $configs
     * @return ContainerBuilder
     */
    private function getContainer(array $configs = array())
    {
        $loader = new JMSSerializerExtension();
        $container = new ContainerBuilder();

        $container->getCompilerPassConfig()->setOptimizationPasses(array(
            new ResolveParameterPlaceHoldersPass(),
            new ResolveDefinitionTemplatesPass(),
        ));
        $container->getCompilerPassConfig()->setRemovingPasses(array(new RemoveUnusedDefinitionsPass()));

        $container->setParameter('kernel.debug', true);
        $container->setParameter('kernel.cache_dir', sys_get_temp_dir() . '/serializer');
        $container->setParameter('kernel.bundles', array());
        $container->setParameter('kernel.bundles', array());

        $loader->load(['jms_serializer' => $configs], $container);

        // remove other subscribers
        foreach (array_keys($container->findTaggedServiceIds('jms_serializer.handler')) as $id) {
            $container->removeDefinition($id);
        }
        foreach (array_keys($container->findTaggedServiceIds('jms_serializer.subscribing_handler')) as $id) {
            $container->removeDefinition($id);
        }

        return $container;
    }

    public function testHandler()
    {
        $container = $this->getContainer();

        $def = new Definition('Foo');
        $def->addTag('jms_serializer.handler', [
            'type' => 'DateTime',
            'format' => 'json',
        ]);
        $container->setDefinition('my_service', $def);

        $pass = new CustomHandlersPass();
        $pass->process($container);

        $args = $container->getDefinition('jms_serializer.handler_registry')->getArguments();

        $this->assertEquals([
            2 => ['DateTime' => ['json' => ['my_service', 'deserializeDateTimeFromjson']]],
            1 => ['DateTime' => ['json' => ['my_service', 'serializeDateTimeTojson']]]
        ], $args[1]);
    }

    public function testHandlerDirection()
    {
        $container = $this->getContainer();

        $def = new Definition('Foo');
        $def->addTag('jms_serializer.handler', [
            'type' => 'DateTime',
            'format' => 'json',
            'direction' => 'SERIALIZATION',
        ]);
        $container->setDefinition('my_service', $def);

        $pass = new CustomHandlersPass();
        $pass->process($container);

        $args = $container->getDefinition('jms_serializer.handler_registry')->getArguments();

        $this->assertEquals([
            1 => ['DateTime' => ['json' => ['my_service', 'serializeDateTimeTojson']]]
        ], $args[1]);
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage The direction "bar" of tag "jms_serializer.handler" of service "my_service" does not exist
     */
    public function testHandlerIncorrectDirection()
    {
        $container = $this->getContainer();

        $def = new Definition('Foo');
        $def->addTag('jms_serializer.handler', [
            'type' => 'DateTime',
            'format' => 'json',
            'direction' => 'bar',
        ]);
        $container->setDefinition('my_service', $def);

        $pass = new CustomHandlersPass();
        $pass->process($container);
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Each tag named "jms_serializer.handler" of service "my_service" must have at least two attributes: "type" and "format"
     */
    public function testHandlerMustHaveTypeAndFormat()
    {
        $container = $this->getContainer();

        $def = new Definition('Foo');
        $def->addTag('jms_serializer.handler');
        $container->setDefinition('my_service', $def);

        $pass = new CustomHandlersPass();
        $pass->process($container);
    }

    public function testSubscribingHandler()
    {
        $container = $this->getContainer();

        $def = new Definition('JMS\SerializerBundle\Tests\DependencyInjection\Fixture\SubscribingHandler');
        $def->addTag('jms_serializer.subscribing_handler');
        $container->setDefinition('my_service', $def);

        $pass = new CustomHandlersPass();
        $pass->process($container);

        $args = $container->getDefinition('jms_serializer.handler_registry')->getArguments();

        $this->assertEquals([
            1 => ['DateTime' => ['json' => ['my_service', 'onDateTime']]]
        ], $args[1]);
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage The service "my_service" must implement the SubscribingHandlerInterface
     */
    public function testSubscribingHandlerInterface()
    {
        $container = $this->getContainer();

        $def = new Definition('JMS\SerializerBundle\Tests\DependencyInjection\Fixture\SimpleObject');
        $def->addTag('jms_serializer.subscribing_handler');
        $container->setDefinition('my_service', $def);

        $pass = new CustomHandlersPass();
        $pass->process($container);
    }
}

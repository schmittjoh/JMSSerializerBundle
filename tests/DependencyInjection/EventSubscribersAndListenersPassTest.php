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

use JMS\SerializerBundle\DependencyInjection\Compiler\RegisterEventListenersAndSubscribersPass;
use JMS\SerializerBundle\DependencyInjection\JMSSerializerExtension;
use Symfony\Component\DependencyInjection\Compiler\RemoveUnusedDefinitionsPass;
use Symfony\Component\DependencyInjection\Compiler\ResolveDefinitionTemplatesPass;
use Symfony\Component\DependencyInjection\Compiler\ResolveParameterPlaceHoldersPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class EventSubscribersAndListenersPassTest extends \PHPUnit_Framework_TestCase
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


        // remove other listeners
        foreach (array_keys($container->findTaggedServiceIds('jms_serializer.event_listener')) as $id) {
            $container->removeDefinition($id);
        }
        // remove other subscribers
        foreach (array_keys($container->findTaggedServiceIds('jms_serializer.event_subscriber')) as $id) {
            $container->removeDefinition($id);
        }

        return $container;
    }

    /**
     * @expectedException RuntimeException
     */
    public function testEventListenerMustHaveEventDefined()
    {
        $container = $this->getContainer();

        $def = new Definition('Foo');
        $def->addTag('jms_serializer.event_listener', [
            'class' => 'Bar',
        ]);

        $container->setDefinition('my_listener', $def);

        $pass = new RegisterEventListenersAndSubscribersPass();
        $pass->process($container);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testEventListenerMustBePublic()
    {
        $container = $this->getContainer();

        $def = new Definition('Foo');
        $def->setPublic(false);
        $def->addTag('jms_serializer.event_listener');

        $container->setDefinition('my_listener', $def);

        $pass = new RegisterEventListenersAndSubscribersPass();
        $pass->process($container);
    }

    public function testEventListener()
    {
        $container = $this->getContainer();

        $def = new Definition('Foo');
        $def->addTag('jms_serializer.event_listener', [
            'event' => 'serializer.pre_serialize',
            'class' => 'Bar',
        ]);

        $container->setDefinition('my_listener', $def);

        $pass = new RegisterEventListenersAndSubscribersPass();
        $pass->process($container);

        $dispatcher = $container->getDefinition('jms_serializer.event_dispatcher');
        $methodCalls = $dispatcher->getMethodCalls();

        $called = false;
        foreach ($methodCalls as $call) {
            if ($call[0] === 'setListeners') {
                $called = true;
                $this->assertEquals([
                    'serializer.pre_serialize' => [
                        [
                            ['my_listener', 'onserializerpreserialize'],
                            'bar',
                            null
                        ]
                    ]], $call[1][0]);
            }
        }

        if (!$called) {
            $this->fail("The method setListeners was not invoked on the jms_serializer.event_dispatcher");
        }
    }

    public function testEventListenerWithParams()
    {
        $container = $this->getContainer();

        $container->setParameter('bar', 'Bar');

        $def = new Definition('Foo');
        $def->addTag('jms_serializer.event_listener', [
            'event' => 'serializer.pre_serialize',
            'class' => '%bar%',
        ]);

        $container->setDefinition('my_listener', $def);

        $pass = new RegisterEventListenersAndSubscribersPass();
        $pass->process($container);

        $dispatcher = $container->getDefinition('jms_serializer.event_dispatcher');
        $methodCalls = $dispatcher->getMethodCalls();

        $called = false;
        foreach ($methodCalls as $call) {
            if ($call[0] === 'setListeners') {
                $called = true;
                $this->assertEquals([
                    'serializer.pre_serialize' => [
                        [
                            ['my_listener', 'onserializerpreserialize'],
                            'bar',
                            null
                        ]
                    ]], $call[1][0]);
            }
        }

        if (!$called) {
            $this->fail("The method setListeners was not invoked on the jms_serializer.event_dispatcher");
        }
    }

    public function testEventSubscriber()
    {
        $container = $this->getContainer();

        $def = new Definition('JMS\SerializerBundle\Tests\DependencyInjection\Fixture\EventSubscriber');
        $def->addTag('jms_serializer.event_subscriber');

        $container->setDefinition('my_listener', $def);

        $pass = new RegisterEventListenersAndSubscribersPass();
        $pass->process($container);

        $dispatcher = $container->getDefinition('jms_serializer.event_dispatcher');
        $methodCalls = $dispatcher->getMethodCalls();

        $called = false;
        foreach ($methodCalls as $call) {
            if ($call[0] === 'setListeners') {
                $called = true;
                $this->assertEquals([
                    'serializer.pre_serialize' => [
                        [
                            ['my_listener', 'onserializerpreserialize'],
                            'bar',
                            'json'
                        ]
                    ]], $call[1][0]);
            }
        }

        if (!$called) {
            $this->fail("The method setListeners was not invoked on the jms_serializer.event_dispatcher");
        }
    }

    /**
     * @expectedException RuntimeException
     */
    public function testEventSubscriberInterface()
    {
        $container = $this->getContainer();

        $def = new Definition('JMS\SerializerBundle\Tests\DependencyInjection\Fixture\VersionedObject');
        $def->addTag('jms_serializer.event_subscriber');

        $container->setDefinition('my_listener', $def);

        $pass = new RegisterEventListenersAndSubscribersPass();
        $pass->process($container);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testEventSubscriberMustBePublic()
    {
        $container = $this->getContainer();

        $def = new Definition('JMS\SerializerBundle\Tests\DependencyInjection\Fixture\VersionedObject');
        $def->setPublic(false);
        $def->addTag('jms_serializer.event_subscriber');

        $container->setDefinition('my_listener', $def);

        $pass = new RegisterEventListenersAndSubscribersPass();
        $pass->process($container);
    }
}


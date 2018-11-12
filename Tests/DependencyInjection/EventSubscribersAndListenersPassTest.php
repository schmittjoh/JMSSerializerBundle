<?php

namespace JMS\SerializerBundle\Tests\DependencyInjection;

use JMS\SerializerBundle\DependencyInjection\Compiler\RegisterEventListenersAndSubscribersPass;
use JMS\SerializerBundle\DependencyInjection\JMSSerializerExtension;
use JMS\SerializerBundle\Tests\DependencyInjection\Fixture\SimpleHandler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class EventSubscribersAndListenersPassTest extends TestCase
{
    /**
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
     * @expectedExceptionMessage The tag "jms_serializer.event_listener" of service "my_listener" requires an attribute named "event".
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

    public function testEventListenerCanBePrivate()
    {
        $container = $this->getContainer();

        $def = new Definition('Foo');
        $def->setPublic(false);
        $def->addTag('jms_serializer.event_listener', ['event' => 'serializer.pre_serialize']);

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
                            null,
                            null
                        ]
                    ]], $call[1][0]);
            }
        }

        if (!$called) {
            $this->fail("The method setListeners was not invoked on the jms_serializer.event_dispatcher");
        }
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
                            'Bar',
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
                            'Bar',
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
                            'Bar',
                            'json',
                            null
                        ]
                    ]], $call[1][0]);
            }
        }

        if (!$called) {
            $this->fail('The method setListeners was not invoked on the jms_serializer.event_dispatcher');
        }
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage The service "my_listener" (class: JMS\SerializerBundle\Tests\DependencyInjection\Fixture\VersionedObject) does not implement the EventSubscriberInterface.
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

    public function testEventSubscriberCanBePrivate()
    {
        $container = $this->getContainer();

        $def = new Definition(SimpleHandler::class);
        $def->setPublic(false);
        $def->addTag('jms_serializer.event_subscriber');

        $container->setDefinition('my_subscriber', $def);

        $pass = new RegisterEventListenersAndSubscribersPass();
        $pass->process($container);

        $dispatcher = $container->getDefinition('jms_serializer.event_dispatcher');
        $methodCalls = $dispatcher->getMethodCalls();

        $called = false;
        foreach ($methodCalls as $call) {
            if ($call[0] === 'setListeners') {
                $called = true;
                $this->assertEquals([
                    'the-event-name' => [
                        [
                            ['my_subscriber', 'onEventName'],
                            'some-class',
                            'json',
                            null
                        ]
                    ]], $call[1][0]);
            }
        }

        if (!$called) {
            $this->fail('The method setListeners was not invoked on the jms_serializer.event_dispatcher');
        }
    }
}


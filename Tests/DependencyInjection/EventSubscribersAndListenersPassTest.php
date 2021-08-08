<?php

declare(strict_types=1);

namespace JMS\SerializerBundle\Tests\DependencyInjection;

use JMS\SerializerBundle\DependencyInjection\Compiler\RegisterEventListenersAndSubscribersPass;
use JMS\SerializerBundle\DependencyInjection\JMSSerializerExtension;
use JMS\SerializerBundle\Tests\DependencyInjection\Fixture\SimpleHandler;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class EventSubscribersAndListenersPassTest extends TestCase
{
    /**
     * @param array $configs
     *
     * @return ContainerBuilder
     */
    private function getContainer(array $configs = [])
    {
        $loader = new JMSSerializerExtension();
        $container = new ContainerBuilder();

        $container->setParameter('kernel.debug', true);
        $container->setParameter('kernel.cache_dir', sys_get_temp_dir() . '/serializer');
        $container->setParameter('kernel.bundles', []);
        $container->setParameter('kernel.bundles', []);

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

    public function testEventListenerMustHaveEventDefined()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The tag "jms_serializer.event_listener" of service "my_listener" requires an attribute named "event".');

        $container = $this->getContainer();

        $def = new Definition('Foo');
        $def->addTag('jms_serializer.event_listener', ['class' => 'Bar']);

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

        $this->assertEquals([
            'serializer.pre_serialize' => [
                [
                    ['my_listener', 'onserializerpreserialize'],
                    null,
                    null,
                    null,
                ],
            ],
        ], $this->getRegisteredListeners($container));
    }

    private function buildListenersFromCalls(array $calls): array
    {
        $listeners = [];
        foreach ($calls as $callData) {
            if ('addListener' !== $callData[0]) {
                continue;
            }

            [$eventName, $callable, $class, $format, $interface] = $callData[1];
            $listeners[$eventName][] = [$callable, $class, $format, $interface];
        }

        return $listeners;
    }

    private function getRegisteredListeners(ContainerBuilder $containerBuilder): array
    {
        $calls = $containerBuilder->findDefinition('jms_serializer.event_dispatcher')->getMethodCalls();

        return $this->buildListenersFromCalls($calls);
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

        $this->assertEquals([
            'serializer.pre_serialize' => [
                [
                    ['my_listener', 'onserializerpreserialize'],
                    'Bar',
                    null,
                    null,
                ],
            ],
        ], $this->getRegisteredListeners($container));
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

        $this->assertEquals([
            'serializer.pre_serialize' => [
                [
                    ['my_listener', 'onserializerpreserialize'],
                    'Bar',
                    null,
                    null,
                ],
            ],
        ], $this->getRegisteredListeners($container));
    }

    public function testEventSubscriber()
    {
        $container = $this->getContainer();

        $def = new Definition('JMS\SerializerBundle\Tests\DependencyInjection\Fixture\EventSubscriber');
        $def->addTag('jms_serializer.event_subscriber');

        $container->setDefinition('my_listener', $def);

        $pass = new RegisterEventListenersAndSubscribersPass();
        $pass->process($container);

        $this->assertEquals([
            'serializer.pre_serialize' => [
                [
                    ['my_listener', 'onserializerpreserialize'],
                    'Bar',
                    'json',
                    null,
                ],
            ],
        ], $this->getRegisteredListeners($container));
    }

    public function testEventSubscriberInterface()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The service "my_listener" (class: JMS\SerializerBundle\Tests\DependencyInjection\Fixture\VersionedObject) does not implement the EventSubscriberInterface.');

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

        $this->assertEquals([
            'the-event-name' => [
                [
                    ['my_subscriber', 'onEventName'],
                    'some-class',
                    'json',
                    null,
                ],
            ],
        ], $this->getRegisteredListeners($container));
    }
}

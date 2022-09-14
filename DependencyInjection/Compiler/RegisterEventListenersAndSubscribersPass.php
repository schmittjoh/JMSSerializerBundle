<?php

declare(strict_types=1);

namespace JMS\SerializerBundle\DependencyInjection\Compiler;

use JMS\Serializer\EventDispatcher\EventDispatcher;
use JMS\SerializerBundle\DependencyInjection\ScopedContainer;
use Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @internal
 */
final class RegisterEventListenersAndSubscribersPass extends PerInstancePass
{
    protected function processInstance(ScopedContainer $container): void
    {
        $listeners = $this->findListeners($container);

        $dispatcherDef = $container->findDefinition('jms_serializer.event_dispatcher');
        $listenerServices = [];

        foreach ($listeners as &$events) {
            $events = array_merge(...$events);
        }

        foreach ($listeners as $event => $listenersPerEvent) {
            foreach ($listenersPerEvent as $singleListener) {
                $id = (string) $singleListener[0][0];

                $listenerServices[$id] = new ServiceClosureArgument($singleListener[0][0]);
                $singleListener[0][0] = $id;

                $dispatcherDef->addMethodCall('addListener', array_merge([$event], $singleListener));
            }
        }

        $container->findDefinition('jms_serializer.event_dispatcher.service_locator')
            ->setArgument(0, $listenerServices);
    }

    private function findListeners(ScopedContainer $container): array
    {
        $listeners = [];

        foreach ($container->findTaggedServiceIds('jms_serializer.event_listener') as $id => $tags) {
            foreach ($tags as $attributes) {
                if (!isset($attributes['event'])) {
                    throw new \RuntimeException(sprintf('The tag "jms_serializer.event_listener" of service "%s" requires an attribute named "event".', $id));
                }

                $class = isset($attributes['class'])
                    ? $container->getParameterBag()->resolveValue($attributes['class'])
                    : null;

                $format = $attributes['format'] ?? null;
                $method = $attributes['method'] ?? EventDispatcher::getDefaultMethodName($attributes['event']);
                $priority = isset($attributes['priority']) ? (int) $attributes['priority'] : 0;
                $interface = $attributes['interface'] ?? null;

                $listeners[$attributes['event']][$priority][] = [[new Reference($id), $method], $class, $format, $interface];
            }
        }

        foreach ($container->findTaggedServiceIds('jms_serializer.event_subscriber') as $id => $tags) {
            $subscriberClass = $container->getDefinition($id)->getClass();

            $subscriberClassReflectionObj = new \ReflectionClass($subscriberClass);

            if (!$subscriberClassReflectionObj->implementsInterface('JMS\Serializer\EventDispatcher\EventSubscriberInterface')) {
                throw new \RuntimeException(sprintf('The service "%s" (class: %s) does not implement the EventSubscriberInterface.', $id, $subscriberClass));
            }

            foreach (call_user_func([$subscriberClass, 'getSubscribedEvents']) as $eventData) {
                if (!isset($eventData['event'])) {
                    throw new \RuntimeException(sprintf('The service "%s" (class: %s) must return an event for each subscribed event.', $id, $subscriberClass));
                }

                $class = $eventData['class'] ?? null;
                $format = $eventData['format'] ?? null;
                $method = $eventData['method'] ?? EventDispatcher::getDefaultMethodName($eventData['event']);
                $priority = isset($eventData['priority']) ? (int) $eventData['priority'] : 0;
                $interface = $eventData['interface'] ?? null;

                $listeners[$eventData['event']][$priority][] = [[new Reference($id), $method], $class, $format, $interface];
            }
        }

        array_walk($listeners, static function (&$value, $key) {
            ksort($value);
        });

        return $listeners;
    }
}

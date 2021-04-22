<?php

declare(strict_types=1);

namespace JMS\SerializerBundle\Debug\EventDispatcher;

use JMS\Serializer\EventDispatcher\Event;
use JMS\Serializer\EventDispatcher\EventDispatcherInterface;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use Psr\Container\ContainerInterface;

final class TraceableEventDispatcher implements EventDispatcherInterface
{
    private $eventDispatcher;
    private $container;

    public function __construct(ContainerInterface $container, EventDispatcherInterface $eventDispatcher)
    {
        $this->container = $container;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function hasListeners(string $eventName, string $class, string $format): bool
    {
        return $this->eventDispatcher->hasListeners($eventName, $class, $format);
    }

    public function dispatch(string $eventName, string $class, string $format, Event $event): void
    {
        $this->eventDispatcher->dispatch($eventName, $class, $format, $event);
    }

    public function addListener(string $eventName, $callable, ?string $class = null, ?string $format = null, ?string $interface = null): void
    {
        $this->eventDispatcher->addListener($eventName, $callable, $class, $format, $interface);
    }

    public function addSubscriber(EventSubscriberInterface $subscriber): void
    {
        $this->eventDispatcher->addSubscriber($subscriber);
    }

    public function getTriggeredListeners(): array
    {
        $result = [];

        foreach ($this->getTraceableListeners() as $eventName => $info) {
            if ($info['calls']) {
                foreach ($info['calls'] as $format => $calls) {
                    $result[$eventName][] = array_merge($info, [
                        'format'        => $format,
                        'calls'         => $calls,
                        'totalDuration' => $this->calculateTotalDuration($calls),
                    ]);
                }
            }
        }

        return $result;
    }

    public function getNotTriggeredListeners(): array
    {
        $result = [];

        foreach ($this->getTraceableListeners() as $eventName => $info) {
            if (!$info['calls']) {
                $result[$eventName][] = $info;
            }
        }

        return $result;
    }

    /**
     * @return TraceableEventListener[]
     */
    private function getTraceableListeners(): iterable
    {
        foreach ($this->eventDispatcher->getListeners() as $event => $listeners) {
            foreach ($listeners as $listener) {
                [$object, $method] = $listener[0];
                if (is_string($object) && $this->container->has($object)) {
                    $object = $this->container->get($object);
                }
                if ($object instanceof TraceableEventListener) {
                    yield $event => [
                        'listener'  => $object->getInnerClass(),
                        'method'    => $method,
                        'format'    => $listener[2],
                        'interface' => $listener[3],
                        'calls'     => $object->getCalls(),
                    ];
                }
            }
        }
    }

    private function calculateTotalDuration(array $calls): float
    {
        return array_sum(array_column($calls, 'duration'));
    }
}

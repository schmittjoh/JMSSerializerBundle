<?php

declare(strict_types=1);

namespace JMS\SerializerBundle\Debug;

use JMS\Serializer\EventDispatcher\LazyEventDispatcher;

/**
 * @internal
 */
final class TraceableEventDispatcher extends LazyEventDispatcher
{
    /**
     * @var array
     */
    private $storage = [];

    public function getTriggeredEvents(): array
    {
        $data = [
            'count' => 0,
            'duration' => 0
        ];

        foreach ($this->storage as $calledOnTypes) {
            foreach ($calledOnTypes as $calls) {
                $data['count'] += count($calls);
                $data['duration'] += $this->calculateTotalDuration($calls);
            }
        }
        return $data;
    }

    public function getTriggeredListeners(): array
    {
        $resultsByListener = [];

        foreach ($this->storage as $eventName => $calledOnTypes) {
            foreach ($calledOnTypes as $type => $calls) {
                foreach ($calls as $call) {
                    $listener = $this->findNameForListener($call['listener']);
                    $resultsByListener[$eventName][$listener][$type][] = $call;
                }
            }
        }

        foreach ($resultsByListener as $eventName => $calledOnListeners) {
            foreach ($calledOnListeners as $listener => $calledOnTypes) {
                foreach ($calledOnTypes as $type => $calls) {
                    $resultsByListener[$eventName][$listener][$type] = [
                        'calls' => count($calls),
                        'duration' => $this->calculateTotalDuration($calls)
                    ];
                }
            }
        }

        return $resultsByListener;
    }

    private function findNameForListener($listener): string
    {
        if (is_array($listener)) {
            return (is_string($listener[0]) ? $listener[0] : get_class($listener[0])) . '::' . $listener[1];
        }
        return 'unknown';
    }

    public function getNotTriggeredListeners(): array
    {
        $result = [];

        foreach ($this->getListeners() as $event => $listeners) {
            foreach ($listeners as $listener) {
                foreach ($this->storage[$event] ?? [] as $calls) {
                    foreach ($calls as $call) {
                        if ($call['listener'] == $listener[0]) {
                            continue 3;
                        }
                    }
                }
                $listenerName = $this->findNameForListener($listener[0]);
                $result[$event][$listenerName] = $listenerName;
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    protected function initializeListeners(string $eventName, string $loweredClass, string $format): array
    {
        $listeners = parent::initializeListeners($eventName, $loweredClass, $format);
        foreach ($listeners as &$listener) {
            $listener[0] = $f = function (...$args) use ($listener, &$f) {
                $t = microtime(true);
                call_user_func_array($listener[0], $args);

                // $args = [$event, $eventName, $class, $format, $dispatcher]
                // $listener = [$callable, $class, $format, $interface]
                $this->storage[$args[1]][$args[2]][] = [
                    'listener' => $listener[0],
                    'format' => $args[3],
                    'type' => $args[0]->getType(),
                    'duration' => microtime(true) - $t
                ];
            };
        }

        return $listeners;
    }

    private function calculateTotalDuration(array $calls): float
    {
        return array_sum(array_column($calls, 'duration')) * 1000;
    }
}

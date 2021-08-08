<?php declare(strict_types=1);

namespace JMS\SerializerBundle\Debug;

use JMS\Serializer\Handler\HandlerRegistryInterface;
use JMS\Serializer\Handler\SubscribingHandlerInterface;

/**
 * @internal
 */
final class TraceableHandlerRegistry implements HandlerRegistryInterface
{
    /**
     * @var array
     */
    private $storage = [];

    private $registry;

    private $registeredHandlers = [];

    public function __construct(HandlerRegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    public function registerSubscribingHandler(SubscribingHandlerInterface $handler): void
    {
        $this->registry->registerSubscribingHandler($handler);
    }

    public function registerHandler(int $direction, string $typeName, string $format, $handler): void
    {
        $this->registry->registerHandler($direction, $typeName, $format, $handler);
        $name = $this->findNameForHandler($handler);
        $this->registeredHandlers[$direction][$typeName][$name] = $name;
        $a = &$this->registeredHandlers[$direction];
        ksort($a);
    }

    public function getHandler(int $direction, string $typeName, string $format)
    {
        $handler = $this->registry->getHandler($direction, $typeName, $format);
        if ($handler === null) {
            return null;
        }
        return function (...$args) use ($handler, $direction, $typeName, $format) {
            try {
                $t = microtime(true);
                return call_user_func_array($handler, $args);
            } finally {
                $this->storage[$direction][$typeName][] = [
                    'handler' => $handler,
                    'format' => $format,
                    'duration' => microtime(true) - $t
                ];
            }
        };
    }

    private function findNameForHandler($listener): string
    {
        if (is_array($listener)) {
            return (is_string($listener[0]) ? $listener[0] : get_class($listener[0])) . '::' . $listener[1];
        }
        return 'unknown';
    }

    public function getTriggeredHandlers(): array
    {
        $result = [];

        foreach ($this->storage as $direction => $handlersByType) {
            foreach ($handlersByType as $type => $calls) {
                foreach ($calls as $call) {
                    $handlerName = $this->findNameForHandler($call['handler']);
                    if (!isset($result[$direction][$type][$handlerName])) {
                        $result[$direction][$type][$handlerName] = [
                            'calls' => 0,
                            'duration' => 0,
                        ];
                    }
                    $result[$direction][$type][$handlerName] = [
                        'handler' => $handlerName,
                        'calls' => $result[$direction][$type][$handlerName]['calls'] + 1,
                        'duration' => $result[$direction][$type][$handlerName]['duration'] + $call['duration'],
                    ];
                }
            }
        }

        return $result;
    }

    public function getNotTriggeredHandlers(): array
    {
        $registered = $this->registeredHandlers;

        foreach ($this->storage as $direction => $handlersByType) {
            foreach ($handlersByType as $type => $calls) {
                foreach ($calls as $call) {
                    $handlerName = $this->findNameForHandler($call['handler']);
                    unset($registered[$direction][$type][$handlerName]);
                }

                if (!count($registered[$direction][$type])) {
                    unset($registered[$direction][$type]);
                }
            }
        }

        return $registered;
    }
}

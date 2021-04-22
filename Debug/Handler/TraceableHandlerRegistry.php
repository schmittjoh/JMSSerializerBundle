<?php declare(strict_types=1);

namespace JMS\SerializerBundle\Debug\Handler;

use JMS\Serializer\Handler\HandlerRegistryInterface;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use Psr\Container\ContainerInterface;

final class TraceableHandlerRegistry implements HandlerRegistryInterface
{
    private $container;
    private $registry;

    public function __construct(ContainerInterface $container, HandlerRegistryInterface $registry)
    {
        $this->container = $container;
        $this->registry = $registry;
    }

    public function registerSubscribingHandler(SubscribingHandlerInterface $handler): void
    {
        $this->registry->registerSubscribingHandler($handler);
    }

    public function registerHandler(int $direction, string $typeName, string $format, $handler): void
    {
        $this->registry->registerHandler($direction, $typeName, $format, $handler);
    }

    public function getHandler(int $direction, string $typeName, string $format)
    {
        $this->registry->getHandler($direction, $typeName, $format);
    }

    public function getTriggeredHandlers(): array
    {
        $result = [];

        foreach ($this->getTraceableHandlers() as $info) {
            if ($info['calls']) {
                foreach ($info['calls'] as $direction => $callsByFormat) {
                    foreach ($callsByFormat as $format => $calls) {
                        if ($info['format'] === $format) {
                            $result[$direction][] = array_merge($info, [
                                'format'        => $format,
                                'calls'         => $calls,
                                'totalDuration' => $this->calculateTotalDuration($calls),
                            ]);
                        }
                    }
                }
            }
        }

        return $result;
    }

    public function getNotTriggeredHandlers(): array
    {
        $result = [];

        foreach ($this->getTraceableHandlers() as $info) {
            if (!$info['calls']) {
                $result[$info['direction']][] = $info;
            }
        }

        return $result;
    }

    /**
     * @return TraceableHandler[]
     */
    private function getTraceableHandlers(): iterable
    {
        foreach ($this->registry->getHandlers() as $direction => $directionHandlers) {
            foreach ($directionHandlers as $class => $handler) {
                foreach ($handler as $format => $config) {
                    [$object, $method] = $config;
                    if (is_string($object) && $this->container->has($object)) {
                        $object = $this->container->get($object);
                    }
                    if ($object instanceof TraceableHandler) {
                        yield [
                            'direction' => $direction,
                            'handler'   => $object->getInnerClass(),
                            'class'     => $class,
                            'method'    => $method,
                            'format'    => $format,
                            'calls'     => $object->getCalls(),
                        ];
                    }
                }
            }
        }
    }

    private function calculateTotalDuration(array $calls): float
    {
        return array_sum(array_column($calls, 'duration'));
    }
}
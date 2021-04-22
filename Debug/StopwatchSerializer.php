<?php declare(strict_types=1);

namespace JMS\SerializerBundle\Debug;

use JMS\Serializer\ArrayTransformerInterface;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\GraphNavigatorInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\Stopwatch\Stopwatch;

class StopwatchSerializer implements SerializerInterface, ArrayTransformerInterface
{
    private $inner;
    private $stopwatch;
    private $collector;

    public function __construct(Serializer $serializer, Stopwatch $stopwatch, RunsCollector $collector)
    {
        $this->inner = $serializer;
        $this->stopwatch = $stopwatch;
        $this->collector = $collector;
    }

    public function toArray($data, ?SerializationContext $context = null, ?string $type = null): array
    {
        return $this->callUserFunction(
            GraphNavigatorInterface::DIRECTION_SERIALIZATION,
            'array',
            [$this->inner, 'toArray'],
            [$data, $context, $type]
        );
    }

    public function fromArray(array $data, string $type, ?DeserializationContext $context = null)
    {
        return $this->callUserFunction(
            GraphNavigatorInterface::DIRECTION_DESERIALIZATION,
            'array',
            [$this->inner, 'fromArray'],
            [$data, $type, $context]
        );
    }

    public function serialize($data, string $format, ?SerializationContext $context = null, ?string $type = null): string
    {
        return $this->callUserFunction(
            GraphNavigatorInterface::DIRECTION_SERIALIZATION,
            $format,
            [$this->inner, 'serialize'],
            [$data, $format, $context, $type]
        );
    }

    public function deserialize(string $data, string $type, string $format, ?DeserializationContext $context = null)
    {
        return $this->callUserFunction(
            GraphNavigatorInterface::DIRECTION_DESERIALIZATION,
            $format,
            [$this->inner, 'deserialize'],
            [$data, $type, $format, $context]
        );
    }

    private function callUserFunction(int $direction, string $format, $callback, array $args = [])
    {
        try {
            $this->stopwatch->start('jms_serializer');
            $this->collector->start($direction, $format);
            return call_user_func_array($callback, $args);
        } finally {
            $this->collector->end();
            $this->stopwatch->stop('jms_serializer');
        }
    }
}

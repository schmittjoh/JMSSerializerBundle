<?php declare(strict_types=1);

namespace JMS\SerializerBundle\Debug\Handler;

use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigatorInterface;
use JMS\Serializer\SerializationContext;
use JMS\SerializerBundle\Debug\TraceableTrait;

final class TraceableHandler
{
    use TraceableTrait;

    public function __construct($handler)
    {
        $this->inner = $handler;
    }

    public function __call(string $method, array $arguments)
    {
        /** @var Context $context */
        $context = $arguments[3];
        $direction = $context instanceof SerializationContext ? GraphNavigatorInterface::DIRECTION_SERIALIZATION : GraphNavigatorInterface::DIRECTION_DESERIALIZATION;

        $call = [
            'start' => microtime(true),
            'class'  => get_class($this->inner),
            'method' => $method,
        ];

        try {
            return call_user_func_array([$this->inner, $method], $arguments);
        } finally {
            $call['duration'] = microtime(true) - $call['start'];
            $this->calls[$direction][$context->getFormat()][] = $call;
        }
    }
}
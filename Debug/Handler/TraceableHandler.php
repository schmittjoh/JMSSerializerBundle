<?php declare(strict_types=1);

namespace JMS\SerializerBundle\Debug\Handler;

use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigatorInterface;
use JMS\Serializer\SerializationContext;
use JMS\SerializerBundle\Debug\TraceableTrait;
use JMS\SerializerBundle\Debug\RunsCollector;

final class TraceableHandler
{
    use TraceableTrait;

    private $collector;

    public function __construct($handler, RunsCollector $collector)
    {
        $this->inner = $handler;
        $this->collector = $collector;
    }

    public function __call(string $method, array $arguments)
    {
        /** @var Context $context */
        $context = $arguments[3];
        $direction = $context instanceof SerializationContext ? GraphNavigatorInterface::DIRECTION_SERIALIZATION : GraphNavigatorInterface::DIRECTION_DESERIALIZATION;


        $this->collector->startHandler($this->getInnerClass());

        $call = [
            'start' => microtime(true),
        ];

        try {
            return call_user_func_array([$this->inner, $method], $arguments);
        } finally {
            $call['duration'] = $this->collector->endHandler();
            $this->calls[$direction][$context->getFormat()][] = $call;
        }
    }
}
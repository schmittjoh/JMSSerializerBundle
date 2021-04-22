<?php declare(strict_types=1);

namespace JMS\SerializerBundle\Debug\EventDispatcher;

use JMS\SerializerBundle\Debug\RunsCollector;
use JMS\SerializerBundle\Debug\TraceableTrait;

final class TraceableEventListener
{
    use TraceableTrait;

    private $collector;

    public function __construct(object $listener, RunsCollector $collector)
    {
        $this->inner = $listener;
        $this->collector = $collector;
    }

    public function __call(string $method, array $arguments)
    {
        $format = $arguments[3];

        $this->collector->startEventListener($arguments[1], $this->getInnerClass(), $method);

        $call = [
            'start' => microtime(true),
        ];

        try {
            return call_user_func_array([$this->inner, $method], $arguments);
        } finally {
            $call['duration'] = $this->collector->endEventListener($arguments[1]);
            $this->calls[$format][] = $call;
        }
    }
}
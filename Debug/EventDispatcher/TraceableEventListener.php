<?php declare(strict_types=1);

namespace JMS\SerializerBundle\Debug\EventDispatcher;

use JMS\SerializerBundle\Debug\TraceableTrait;

final class TraceableEventListener
{
    use TraceableTrait;

    public function __construct(object $listener)
    {
        $this->inner = $listener;
    }

    public function __call(string $method, array $arguments)
    {
        $format = $arguments[3];

        $call = [
            'start'  => microtime(true),
            'class'  => get_class($this->inner),
            'method' => $method,
        ];

        try {
            return call_user_func_array([$this->inner, $method], $arguments);
        } finally {
            $call['duration'] = microtime(true) - $call['start'];
            $this->calls[$format][] = $call;
        }
    }
}
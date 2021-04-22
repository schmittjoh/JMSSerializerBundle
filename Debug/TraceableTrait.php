<?php declare(strict_types=1);

namespace JMS\SerializerBundle\Debug;

trait TraceableTrait
{
    /** @var object */
    private $inner;

    /** @var array */
    private $calls = [];

    public function getCalls(): array
    {
        return $this->calls;
    }

    public function getInnerClass(): string
    {
        return get_class($this->inner);
    }
}

<?php declare(strict_types=1);

namespace JMS\SerializerBundle\Debug\Visitor\Factory;

use JMS\Serializer\Visitor\Factory\SerializationVisitorFactory;
use JMS\Serializer\Visitor\SerializationVisitorInterface;
use JMS\SerializerBundle\Debug\Visitor\TraceableSerializationVisitor;
use JMS\SerializerBundle\Debug\RunsCollector;

final class TraceableSerializationVisitorFactory implements SerializationVisitorFactory
{
    private $inner;
    private $collector;

    public function __construct(SerializationVisitorFactory $inner, RunsCollector $collector)
    {
        $this->inner = $inner;
        $this->collector = $collector;
    }

    public function getVisitor(): SerializationVisitorInterface
    {
        return new TraceableSerializationVisitor($this->inner->getVisitor(), $this->collector);
    }
}
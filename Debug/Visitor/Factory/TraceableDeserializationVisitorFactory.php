<?php declare(strict_types=1);

namespace JMS\SerializerBundle\Debug\Visitor\Factory;

use JMS\Serializer\Visitor\DeserializationVisitorInterface;
use JMS\Serializer\Visitor\Factory\DeserializationVisitorFactory;
use JMS\SerializerBundle\Debug\Visitor\TraceableDeserializationVisitor;
use JMS\SerializerBundle\Debug\RunsCollector;

final class TraceableDeserializationVisitorFactory implements DeserializationVisitorFactory
{
    private $inner;
    private $collector;

    public function __construct(DeserializationVisitorFactory $inner, ?RunsCollector $collector = null)
    {
        $this->inner = $inner;
        $this->collector = $collector;
    }

    public function getVisitor(): DeserializationVisitorInterface
    {
        return new TraceableDeserializationVisitor($this->inner->getVisitor(), $this->collector);
    }
}
<?php declare(strict_types=1);

namespace JMS\SerializerBundle\Debug\Visitor;

use JMS\Serializer\GraphNavigatorInterface;
use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Metadata\PropertyMetadata;
use JMS\Serializer\Visitor\SerializationVisitorInterface;
use JMS\SerializerBundle\Debug\RunsCollector;

final class TraceableSerializationVisitor implements SerializationVisitorInterface
{
    use TraceableVisitorTrait;

    public function __construct(SerializationVisitorInterface $inner, RunsCollector $collector)
    {
        $this->inner = $inner;
        $this->collector = $collector;
    }

    public function visitNull($data, array $type)
    {
        return $this->inner->visitNull($data, $type);
    }

    public function visitString($data, array $type)
    {
        return $this->inner->visitString($data, $type);
    }

    public function visitBoolean($data, array $type)
    {
        return $this->inner->visitBoolean($data, $type);
    }

    public function visitDouble($data, array $type)
    {
        return $this->inner->visitDouble($data, $type);
    }

    public function visitInteger($data, array $type)
    {
        return $this->inner->visitInteger($data, $type);
    }

    public function visitArray($data, array $type)
    {
        return $this->doVisitArray($data, $type);
    }

    public function startVisitingObject(ClassMetadata $metadata, object $data, array $type): void
    {
        $this->doStartVisitingObject($metadata, $data, $type);
    }

    public function endVisitingObject(ClassMetadata $metadata, $data, array $type)
    {
        return $this->doEndVisitingObject($metadata, $data, $type);
    }

    public function visitProperty(PropertyMetadata $metadata, $data): void
    {
        $this->doVisitProperty($metadata, $data);
    }

    public function prepare($data)
    {
        return $this->inner->prepare($data);
    }

    public function getResult($data)
    {
        return $this->inner->getResult($data);
    }

    public function setNavigator(GraphNavigatorInterface $navigator): void
    {
        $this->inner->setNavigator($navigator);
    }
}
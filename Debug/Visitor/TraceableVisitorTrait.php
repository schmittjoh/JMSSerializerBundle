<?php declare(strict_types=1);

namespace JMS\SerializerBundle\Debug\Visitor;

use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Metadata\PropertyMetadata;
use JMS\SerializerBundle\Debug\RunsCollector;

trait TraceableVisitorTrait
{
    private $inner;
    /** @var RunsCollector */
    private $collector;

    private function doVisitArray($data, $type)
    {
        $this->collector->startVisitingArray($data, $type);
        try {
            return $this->inner->visitArray($data, $type);
        } finally {
            $this->collector->endVisitingArray();
        }
    }

    private function doVisitProperty(PropertyMetadata $metadata, $data)
    {
        $this->collector->startVisitingProperty($metadata, $data);
        try {
            return $this->inner->visitProperty($metadata, $data);
        } finally {
            $this->collector->endVisitingProperty($metadata, $data);
        }
    }

    private function doStartVisitingObject(ClassMetadata $metadata, object $data, array $type)
    {
        $this->collector->startVisitingObject($metadata, $data, $type);

        return $this->inner->startVisitingObject($metadata, $data, $type);
    }

    private function doEndVisitingObject(ClassMetadata $metadata, $data, array $type)
    {
        try {
            return $this->inner->endVisitingObject($metadata, $data, $type);
        } finally {
            $this->collector->endVisitingObject($metadata, $data, $type);
        }
    }
}
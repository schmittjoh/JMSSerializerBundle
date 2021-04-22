<?php declare(strict_types=1);

namespace JMS\SerializerBundle\Debug\Metadata;

use JMS\Serializer\Metadata\Driver\AnnotationDriver;
use Metadata\ClassMetadata as BaseClassMetadata;

class TraceableAnnotationDriver extends AnnotationDriver
{
    use MetadataCollectorAwareTrait;

    public function loadMetadataForClass(\ReflectionClass $class): ?BaseClassMetadata
    {
        $trace = [
            'format' => 'annotation',
            'class'  => $class->getName(),
            'start'  => microtime(true),
        ];

        try {
            return parent::loadMetadataForClass($class);
        } finally {
            $trace['duration'] = microtime(true) - $trace['start'];
            $this->collector->addMetadataLoad($trace);
        }
    }
}

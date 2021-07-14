<?php declare(strict_types=1);

namespace JMS\SerializerBundle\Debug\Metadata;

use JMS\Serializer\Metadata\Driver\YamlDriver;
use Metadata\ClassMetadata as BaseClassMetadata;

class TraceableYamlDriver extends YamlDriver
{
    use MetadataCollectorAwareTrait;

    protected function loadMetadataFromFile(\ReflectionClass $class, string $file): ?BaseClassMetadata
    {
        $trace = [
            'format' => 'yaml',
            'class'  => $class->getName(),
            'file'   => $file,
            'start'  => microtime(true),
        ];

        try {
            return parent::loadMetadataFromFile($class, $file);
        } finally {
            $trace['duration'] = microtime(true) - $trace['start'];
            $this->collector->addMetadataLoad($trace);
        }
    }
}

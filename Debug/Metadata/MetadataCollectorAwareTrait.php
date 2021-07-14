<?php declare(strict_types=1);

namespace JMS\SerializerBundle\Debug\Metadata;

trait MetadataCollectorAwareTrait
{
    /** @var MetadataCollector */
    private $collector;

    public function setCollector(MetadataCollector $collector)
    {
        $this->collector = $collector;
    }
}

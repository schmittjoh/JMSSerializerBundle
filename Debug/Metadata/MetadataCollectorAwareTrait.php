<?php declare(strict_types=1);

namespace JMS\SerializerBundle\Debug\Metadata;

use JMS\SerializerBundle\Debug\RunsCollector;

trait MetadataCollectorAwareTrait
{
    /** @var RunsCollector */
    private $collector;

    public function setCollector(RunsCollector $collector)
    {
        $this->collector = $collector;
    }
}

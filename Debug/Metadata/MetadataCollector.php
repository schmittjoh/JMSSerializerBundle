<?php

declare(strict_types=1);

namespace JMS\SerializerBundle\Debug\Metadata;

final class MetadataCollector
{
    private $loadedMetadata = [];

    public function addMetadataLoad(array $trace): void
    {
        $this->loadedMetadata[] = $trace;
    }

    public function getLoadedMetadata(): array
    {
        return $this->loadedMetadata;
    }
}
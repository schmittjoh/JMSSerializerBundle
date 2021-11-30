<?php declare(strict_types=1);

namespace JMS\SerializerBundle\Debug;

use Metadata\Cache\CacheInterface;
use Metadata\ClassMetadata;

/**
 * @internal
 */
final class TraceableDriver implements CacheInterface
{
    /**
     * @var CacheInterface
     */
    private $driver;
    private $storage = [];

    public function __construct(CacheInterface $driver)
    {

        $this->driver = $driver;
    }

    public function getLoadedMetadata()
    {
        return $this->storage;
    }

    public function load($class): ?ClassMetadata
    {
        $metadata = null;
            
        try{
            return $metadata = $this->driver->load($class);
        } finally {
            if ($metadata){
                $this->trackMetadata($metadata);
            }
        }

    }

    private function trackMetadata(ClassMetadata $metadata): void
    {
        $class = $metadata->name;
        $this->storage[$class] = array_merge(
            $this->storage[$class] ?? [], $metadata->fileResources
        );
        $this->storage[$class] = array_unique($this->storage[$class]);
    }

    public function put(ClassMetadata $metadata): void
    {
        $this->driver->put($metadata);
        $this->trackMetadata($metadata);
    }

    public function evict(string $class): void
    {
        $this->driver->evict($class);
    }
}

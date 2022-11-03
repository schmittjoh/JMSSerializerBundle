<?php declare(strict_types=1);

namespace JMS\SerializerBundle\Debug;

use Metadata\AdvancedMetadataFactoryInterface;
use Metadata\ClassHierarchyMetadata;
use Metadata\ClassMetadata;
use Metadata\MergeableClassMetadata;

/**
 * @internal
 */
final class TraceableMetadataFactory implements AdvancedMetadataFactoryInterface
{
    private $storage = [];
    
    /**
     * @var AdvancedMetadataFactoryInterface
     */
    private $metadataFactory;

    public function __construct(AdvancedMetadataFactoryInterface $metadataFactory)
    {
        $this->metadataFactory = $metadataFactory;
    }

    public function getLoadedMetadata(): array
    {
        return $this->storage;
    }
    
    public function getAllClassNames(): array
    {
        return $this->metadataFactory->getAllClassNames();
    }

    /**
     * @return ClassHierarchyMetadata|MergeableClassMetadata|null
     */
    public function getMetadataForClass(string $className)
    {
        $metadata = $this->metadataFactory->getMetadataForClass($className);
        if ($metadata instanceof ClassMetadata) {
            $this->trackMetadata($metadata);
        }
        
        return $metadata;
    }
    
    protected function trackMetadata(ClassMetadata $metadata): void
    {
        $class = $metadata->name;
        $this->storage[$class] = array_merge(
            $this->storage[$class] ?? [], $metadata->fileResources
        );
        $this->storage[$class] = array_unique($this->storage[$class]);
    }
}

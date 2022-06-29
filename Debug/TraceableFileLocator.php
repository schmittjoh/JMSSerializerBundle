<?php declare(strict_types=1);

namespace JMS\SerializerBundle\Debug;

use Metadata\Driver\AdvancedFileLocatorInterface;

/**
 * @internal
 */
final class TraceableFileLocator implements AdvancedFileLocatorInterface
{
    /**
     * @var AdvancedFileLocatorInterface
     */
    private $decorated;
    private $files = [];

    public function __construct(AdvancedFileLocatorInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    public function getAttemptedFiles(): array
    {
        return $this->files;
    }

    public function findFileForClass(\ReflectionClass $class, string $extension): ?string
    {
        $path = $this->decorated->findFileForClass($class, $extension);

        if ($path !== null) {
            $this->files[$class->getName()][] = $path;
        }

        return $path;
    }

    public function findAllClasses(string $extension): array
    {
        return $this->decorated->findAllClasses($extension);
    }
}

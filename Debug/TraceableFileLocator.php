<?php declare(strict_types=1);

namespace JMS\SerializerBundle\Debug;

use Metadata\Driver\AdvancedFileLocatorInterface;
use Metadata\Driver\TraceableFileLocatorInterface;

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

        if ($this->decorated instanceof TraceableFileLocatorInterface) {
            $this->files[$class->getName()] = array_merge($this->files[$class->getName()] ?? [], $this->decorated->getPossibleFilesForClass($class, $extension));
        } elseif ($path !== null) {
            $this->files[$class->getName()][$path] = true;
        }

        return $path;
    }

    public function findAllClasses(string $extension): array
    {
        return $this->decorated->findAllClasses($extension);
    }
}

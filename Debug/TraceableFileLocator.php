<?php declare(strict_types=1);

namespace JMS\SerializerBundle\Debug;

use Metadata\Driver\FileLocator;

/**
 * @internal
 */
final class TraceableFileLocator extends FileLocator
{
    private $files = [];

    public function __construct(array $dirs)
    {
        parent::__construct($dirs);
    }

    public function getAttemptedFiles()
    {
        return $this->files;
    }

    protected function loadFileIfFound($prefix, $dir, \ReflectionClass $class, $extension)
    {
        $pathData = parent::loadFileIfFound($prefix, $dir, $class, $extension);

        if ($pathData[0] !== null) {
            $this->files[$class->getName()][$pathData[0]] = $pathData[1];
        }
        return $pathData;
    }
}

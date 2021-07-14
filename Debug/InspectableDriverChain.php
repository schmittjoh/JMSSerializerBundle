<?php

declare(strict_types=1);

namespace JMS\SerializerBundle\Debug;

use Metadata\ClassMetadata;
use Metadata\Driver\AdvancedDriverInterface;
use Metadata\Driver\DriverInterface;

final class InspectableDriverChain implements AdvancedDriverInterface
{
    /** @var AdvancedDriverInterface[] */
    private $drivers;

    /** @var Collector */
    private $collector;

    public function __construct(Collector $collector, array $drivers = [])
    {
        $this->drivers = $drivers;
        $this->collector = $collector;
    }
    
    public function addDriver(DriverInterface $driver)
    {
        $this->drivers[] = $driver;
    }

    public function loadMetadataForClass(\ReflectionClass $class): ?ClassMetadata
    {
        foreach ($this->drivers as $driver) {
            $this->collector->addMetadataLoad($class->getName(), get_class($driver), false);
            if (null !== $metadata = $driver->loadMetadataForClass($class)) {
                $this->collector->addMetadataLoad($class->getName(), get_class($driver), $metadata);
                return $metadata;
            }
        }

        return null;
    }

    public function getAllClassNames(): array
    {
        $classes = array();
        foreach ($this->drivers as $driver) {
            if (!$driver instanceof AdvancedDriverInterface) {
                throw new \RuntimeException(
                    sprintf(
                        'Driver "%s" must be an instance of "AdvancedDriverInterface" to use '.
                        '"DriverChain::getAllClassNames()".',
                        get_class($driver)
                    )
                );
            }
            $driverClasses = $driver->getAllClassNames();
            if (!empty($driverClasses)) {
                $classes = array_merge($classes, $driverClasses);
            }
        }

        return $classes;
    }
}

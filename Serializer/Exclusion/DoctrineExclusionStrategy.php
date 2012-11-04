<?php

/*
 * Copyright 2012 Vladimir Schmidt <morgen2009@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace JMS\SerializerBundle\Serializer\Exclusion;

use JMS\SerializerBundle\Metadata\ClassMetadata;
use JMS\SerializerBundle\Metadata\PropertyMetadata;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * Exclusion strategy for de-/serialization of Doctrine entities.
 *
 * @note Briefly, if the identificator is empty (or zero), then all
 * properties of the entity will be traversed, otherwise only the identificator
 *
 * @author Vladimir Schmidt <morgen2009@gmail.com>
 */
class DoctrineExclusionStrategy implements ExclusionStrategyInterface
{
    /**
     * @var Doctrine\Common\Persistence\ManagerRegistry
     */
    private $managerRegistry;

    /**
     * @param ManagerRegistry $managerRegistry
     */
    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * Exception classes, those properties will be serialized without
     * taking into account the identificator
     *
     * @var array<string>
     */
    private $exposeClasses = array();

    /**
     * Add class into the exception list
     *
     * @param string  $className class name without leading "\" (like get_class() returns)
     * @param boolean $clear remove previosly added classes
     */
    public function setExposeClass($className, $clear = false)
    {
        if ($clear) {
            $this->exposeClasses = array();
        }
        if (strlen($className) > 0) {
            $this->exposeClasses[$className] = true;
        }
    }

    /**
     * Check if class is in the exception list
     *
     * @param string $className
     */
    public function isExposeClass($className)
    {
        return isset($this->exposeClasses[$className]);
    }

    /**
     * Get array of properties for given object to de-/serialize
     *
     * @param string $class
     * @param object $object
     * @return array<string> | null The value null means all object properties
     */
    public function getPassProperties($class, $object)
    {
        if ($this->isExposeClass($class) || $object == null) {
            return false;
        }

        // Locate possible ObjectManager
        $objectManager = $this->managerRegistry->getManagerForClass($class);

        if (!$objectManager) {
            return false;
        }

        // Locate possible ClassMetadata
        $classMetadataFactory = $objectManager->getMetadataFactory();

        if ($classMetadataFactory->isTransient($class)) {
            return false;
        }

        // Entity update, load it from database
        $classMetadata         = $objectManager->getClassMetadata($class);
        $identifierList        = $classMetadata->getIdentifierFieldNames();
        $identifierValuesList  = $classMetadata->getIdentifierValues($object);
        $missingIdentifierList = array_filter(
            $identifierList,
            function ($identifier) use ($identifierValuesList) {
                return !isset($identifierValuesList[$identifier]);
            }
        );

        if (count($missingIdentifierList) > 0) {
            return false;
        } else {
            return $identifierList;
        }
    }

    /**
     * {@inheritDoc}
     */
    function shouldSkipClass(ClassMetadata $metadata, $object = null)
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    function shouldSkipProperty(PropertyMetadata $property, $object = null)
    {
        $passList = $this->getPassProperties($property->class, $object);
        if ($passList == false) {
            return false;
        } else {
            return array_search($property->name, $passList) === false;
        }
    }
}
?>

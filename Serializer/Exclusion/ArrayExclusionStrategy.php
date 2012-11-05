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
 * Exclusion strategy for de-/serialization of properties specified in array.
 *
 * @author Vladimir Schmidt <morgen2009@gmail.com>
 */
class ArrayExclusionStrategy implements ExclusionStrategyInterface
{
    /**
     * Array of properties, that will be de-/serialized
     *
     * @var array<string>
     */
    private $properties;

    /**
     * @param array<string> $properties
     */
    public function __construct(array $properties)
    {
        $this->properties = $properties;
    }

    /**
     * {@inheritDoc}
     */
    public function shouldSkipClass(ClassMetadata $metadata, $object = null)
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function shouldSkipProperty(PropertyMetadata $property, $object = null)
    {
        return array_search($property->name, $this->properties) === false;
    }
}
?>

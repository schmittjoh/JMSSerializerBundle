<?php

/*
 * Copyright 2011 Johannes M. Schmitt <schmittjoh@gmail.com>
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

namespace JMS\SerializerBundle\Serializer\Normalizer;

use Metadata\MetadataFactoryInterface;
use JMS\SerializerBundle\Annotation\Type;
use JMS\SerializerBundle\Annotation\ExclusionPolicy;
use JMS\SerializerBundle\Exception\InvalidArgumentException;
use JMS\SerializerBundle\Exception\RuntimeException;
use JMS\SerializerBundle\Exception\UnsupportedException;
use JMS\SerializerBundle\Serializer\Exclusion\ExclusionStrategyFactoryInterface;
use JMS\SerializerBundle\Serializer\Exclusion\ExclusionStrategyInterface;
use JMS\SerializerBundle\Serializer\InstanceCreatorInterface;
use JMS\SerializerBundle\Serializer\Naming\PropertyNamingStrategyInterface;

use Symfony\Component\Serializer\Normalizer\SerializerAwareNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Generic normalizer based on class properties.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class PropertyBasedNormalizer extends SerializerAwareNormalizer
{
    private $metadataFactory;
    private $propertyNamingStrategy;
    private $exclusionStrategyFactory;
    private $instanceCreator;

    public function __construct(MetadataFactoryInterface $metadataFactory, PropertyNamingStrategyInterface $propertyNamingStrategy, InstanceCreatorInterface $instanceCreator, ExclusionStrategyFactoryInterface $exclusionStrategyFactory)
    {
        $this->metadataFactory = $metadataFactory;
        $this->propertyNamingStrategy = $propertyNamingStrategy;
        $this->exclusionStrategyFactory = $exclusionStrategyFactory;
        $this->instanceCreator = $instanceCreator;
    }

    /**
     * {@inheritDoc}
     */
    public function normalize($object, $format = null)
    {
        if (!is_object($object)) {
            throw new UnsupportedException(sprintf('Type "%s" is not supported.', gettype($object)));
        }

        $normalized = array();
        $metadata = $this->metadataFactory->getMetadataForClass(get_class($object));

        foreach ($metadata->classMetadata as $classMetadata) {
            $exclusionStrategy = $this->exclusionStrategyFactory->getStrategy($classMetadata->exclusionPolicy);
            foreach ($classMetadata->propertyMetadata as $propertyMetadata) {
                if ($exclusionStrategy->shouldSkipProperty($propertyMetadata)) {
                    continue;
                }

                $value = $this->serializer->normalize($propertyMetadata->reflection->getValue($object), $format);

                // skip null-value properties
                if (null === $value) {
                    continue;
                }

                $normalized[$this->propertyNamingStrategy->translateName($propertyMetadata)] = $value;
            }
        }

        return $normalized;
    }

    /**
     * {@inheritDoc}
     */
    public function denormalize($data, $type, $format = null)
    {
        if (!class_exists($type)) {
            throw new UnsupportedException(sprintf('Unsupported type; "%s" is not a valid class.', $type));
        }

        $metadata = $this->metadataFactory->getMetadataForClass($type);
        $object = $this->instanceCreator->createInstance(end($metadata->classMetadata)->reflection);

        foreach ($metadata->classMetadata as $classMetadata) {
            $exclusionStrategy = $this->exclusionStrategyFactory->getStrategy($classMetadata->exclusionPolicy);

            foreach ($classMetadata->propertyMetadata as $propertyMetadata) {
                if ($exclusionStrategy->shouldSkipProperty($propertyMetadata)) {
                    continue;
                }

                $serializedName = $this->propertyNamingStrategy->translateName($propertyMetadata);
                if(!array_key_exists($serializedName, $data)) {
                    continue;
                }

                if (null === $type = $propertyMetadata->type) {
                    throw new RuntimeException(sprintf('You must define the type for %s::$%s.', $propertyMetadata->class, $propertyMetadata->name));
                }

                $value = $this->serializer->denormalize($data[$serializedName], $type, $format);
                $propertyMetadata->reflection->setValue($object, $value);
            }
        }

        return $object;
    }

    /**
     * {@inheritDoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return is_object($data);
    }

    /**
     * {@inheritDoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return class_exists($type);
    }
}

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

namespace JMS\SerializerBundle\Serializer\Handler;

use Doctrine\Common\Persistence\Proxy;

use Doctrine\ORM\Proxy\Proxy as ORMProxy;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\MappingException;

use JMS\SerializerBundle\Exception\RuntimeException;
use JMS\SerializerBundle\Serializer\VisitorInterface;
use JMS\SerializerBundle\Serializer\Handler\SerializationHandlerInterface;
use JMS\SerializerBundle\Serializer\GenericSerializationVisitor;
use JMS\SerializerBundle\Serializer\XmlSerializationVisitor;


class DoctrineProxyHandler implements SerializationHandlerInterface
{
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function serialize(VisitorInterface $visitor, $data, $type, &$handled)
    {
        if (($data instanceof Proxy || $data instanceof ORMProxy) && (!$data->__isInitialized__ || get_class($data) === $type)) {
            $handled = true;

            if (!$data->__isInitialized__) {
                $data->__load();
            }

            $navigator = $visitor->getNavigator();
            $navigator->detachObject($data);

            // pass the parent class not to load the metadata for the proxy class
            return $navigator->accept($data, get_parent_class($data), $visitor);
        }

        return null;
    }

    public function deserialize(VisitorInterface $visitor, $data, $type, &$visited)
    {
        // Avoid usage of this handler under custom visitors
        if ($visitor instanceof GenericDeserializationVisitor || $visitor instanceof XmlDeserializationVisitor) {
            return;
        }

        // Is it a valid proxy?
        if (!class_exists($type)) {
            return;
        }

        try {
            $classMetadata = $this->entityManager->getClassMetadata($type);
        } catch (MappingException $exception) {
            return;
        }

        // Avoid deserializing if exclusion strategy is applied
        $navigator          = $visitor->getNavigator();
        $exclusionStrategy  = $navigator->getExclusionStrategy();
        $serializerMetadata = $navigator->getMetadataFactory()->getMetadataForClass($type);

        if (null !== $exclusionStrategy && $exclusionStrategy->shouldSkipClass($serializerMetadata)) {
            return;
        }

        // Loading proxy
        $visited = true;
        $entity  = $this->entityManager->find($type, $data);

        if (!$entity) {
            throw new RuntimeException(sprintf('Unable to retrieve unexistent entity "%s".', $type));
        }

        $visitor->setCurrentObject($entity);

        if (null === $visitor->getResult()) {
            $visitor->setResult($entity);
        }

        // Load information for properties
        foreach ($serializerMetadata->propertyMetadata as $propertyMetadata) {
            if (null !== $exclusionStrategy && $exclusionStrategy->shouldSkipProperty($propertyMetadata)) {
                continue;
            }

            if ($propertyMetadata->readOnly) {
                continue;
            }

            // try custom handler
            if (!$visitor->visitPropertyUsingCustomHandler($propertyMetadata, $data)) {
                $visitor->visitProperty($propertyMetadata, $data);
            }
        }

        // Finish object visiting
        $result = $visitor->endVisitingObject($serializerMetadata, $data, $type);

        foreach ($serializerMetadata->postDeserializeMethods as $method) {
            $method->invoke($result);
        }

        return $entity;
    }
}

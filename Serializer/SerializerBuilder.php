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

namespace JMS\SerializerBundle\Serializer;

use JMS\SerializerBundle\Exception\UnsupportedFormatException;
use Metadata\MetadataFactoryInterface;
use JMS\SerializerBundle\Exception\InvalidArgumentException;
use JMS\SerializerBundle\Serializer\Exclusion\VersionExclusionStrategy;
use JMS\SerializerBundle\Serializer\Exclusion\GroupsExclusionStrategy;
use JMS\SerializerBundle\Serializer\Exclusion\ChainExclusionStrategy;
use JMS\SerializerBundle\Serializer\Exclusion\ExclusionStrategyInterface;
use JMS\SerializerBundle\Serializer\Handler\HandlerRegistryInterface;
use JMS\SerializerBundle\Serializer\EventDispatcher\EventDispatcherInterface;
use JMS\SerializerBundle\Serializer\Construction\ObjectConstructorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

/**
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
class SerializerBuilder implements ContainerAwareInterface
{
    private $class;
    private $factory;
    private $serializationVisitors;
    private $deserializationVisitors;
    private $exclusionStrategy;
    private $container;

    public function __construct(MetadataFactoryInterface $factory, HandlerRegistryInterface $handlerRegistry, ObjectConstructorInterface $objectConstructor, EventDispatcherInterface $dispatcher = null, TypeParser $typeParser = null, array $serializationVisitors = array(), array $deserializationVisitors = array(), $class = null, ExclusionStrategyInterface $defaultExclusionStrategy = null)
    {
        $this->class = $class ?: 'JMS\SerializerBundle\Serializer\Serializer';
        $this->factory = $factory;
        $this->serializationVisitors = $serializationVisitors;
        $this->deserializationVisitors = $deserializationVisitors;
        $this->exclusionStrategy = $defaultExclusionStrategy;

        $this->handlerRegistry = $handlerRegistry;
        $this->objectConstructor = $objectConstructor;
        $this->dispatcher = $dispatcher;
        $this->typeParser = $typeParser;
    }

    public function getSerializer()
    {
        $class = $this->class;

        $serializer = new $class($this->factory, $this->handlerRegistry, $this->objectConstructor, $this->dispatcher, $this->typeParser, $this->serializationVisitors, $this->deserializationVisitors, $this->exclusionStrategy);

        if ($this->container && $serializer instanceof ContainerAwareInterface) {
            $serializer->setContainer($this->container);
        }

        return $serializer;
    }

    public function setClass($class)
    {
        $this->class = $class;

        return $this;
    }

    public function setExclusionStrategy(ExclusionStrategyInterface $exclusionStrategy = null)
    {
        $this->exclusionStrategy = $exclusionStrategy;

        return $this;
    }

    public function addExclusionStrategy(ExclusionStrategyInterface $strategy)
    {
        if ($this->exclusionStrategy instanceof ChainExclusionStrategy) {
            $this->exclusionStrategy->addExclusionStrategy($strategy);
        } elseif ($this->exclusionStrategy) {
            $this->exclusionStrategy = new ChainExclusionStrategy(array($this->exclusionStrategy, $strategy));
        } else {
            $this->exclusionStrategy = $strategy;
        }

        return $this;
    }

    public function setVersion($version)
    {
        if (null === $version) {
            if ($this->exclusionStrategy instanceof ChainExclusionStrategy) {
                $this->exclusionStrategy->removeExclusionStrategy('JMS\SerializerBundle\Serializer\Exclusion\VersionExclusionStrategy');
            } else {
                $this->exclusionStrategy = null;
            }

            return $this;
        }

        $strategy = new VersionExclusionStrategy($version);
        $this->addExclusionStrategy($strategy);

        return $this;
    }

    public function setGroups($groups = array())
    {
        $strategy = new GroupsExclusionStrategy((array) $groups);
        $this->addExclusionStrategy($strategy);

        return $this;
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}

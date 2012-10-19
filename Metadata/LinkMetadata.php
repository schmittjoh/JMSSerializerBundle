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

namespace JMS\SerializerBundle\Metadata;

/**
 * Metadata for link
 *
 * @author Miha Vrhovnik <miha.vrhovnik@naviter.com>
 *
 */
class LinkMetadata implements \Serializable
{
    static $DEFAULT_TYPE = 'property';
    static $TYPES = array('property', 'method', 'static');

    /** @var string */
    public $route;
    /** @var boolean */
    public $absolute;
    /** @var array */
    public $parameters;
    /** @var string */
    public $rel;
    /** @var string */
    public $collectionNodeName;
    /** @var string */
    public $nodeName;
    /** @var bool */
    public $templated;
    /** @var string */
    public $name;

    public function __construct($route, $absolute, array $parameters, $rel, $collectionNodeName, $nodeName, $templated, $name)
    {
        $this->route = $route;
        $this->absolute = $absolute;
        $this->parameters = $parameters;
        $this->rel = $rel;
        $this->collectionNodeName = $collectionNodeName;
        $this->nodeName = $nodeName;
        $this->templated = $templated;
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize(array(
            $this->route,
            $this->absolute,
            $this->parameters,
            $this->rel,
            $this->collectionNodeName,
            $this->nodeName,
            $this->templated,
            $this->name,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        list(
            $this->route,
            $this->absolute,
            $this->parameters,
            $this->rel,
            $this->collectionNodeName,
            $this->nodeName,
            $this->templated,
            $this->name,
        ) = unserialize($serialized);
    }

    /**
     * @return string
     */
    public function getRouteName()
    {
        return $this->route;
    }

    /**
     * @return null|string
     */
    public function getLinkRel()
    {
        return $this->rel;
    }

    /**
     * @return bool
     */
    public function generateAbsolute()
    {
        return $this->absolute;
    }

    /**
     * @return array
     */
    public function getRouteParameters()
    {
        return $this->parameters;
    }

    /**
     * @return string
     */
    public function getCollectionNodeName()
    {
        return is_null($this->collectionNodeName) || ('' == $this->collectionNodeName) ? 'links' : $this->collectionNodeName;
    }

    /**
     * @return string
     */
    public function getNodeName()
    {
        return is_null($this->nodeName) || ('' == $this->nodeName) ? 'link' : $this->nodeName;
    }

    public function isTemplated()
    {
        return is_bool($this->templated) ? $this->templated : false;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }
}

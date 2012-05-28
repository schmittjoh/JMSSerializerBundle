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

namespace JMS\SerializerBundle\Annotation;

use JMS\SerializerBundle\Exception\InvalidArgumentException;
use JMS\SerializerBundle\Metadata\LinkMetadata;

/**
 * @Annotation
 * @Target({"CLASS"})
 *
 * @author Miha Vrhovnik <miha.vrhovnik@naviter.com>
 */
final class Link
{
    /** @var string */
    private $route;
    /** @var bool */
    private $absolute;
    /** @var array */
    private $parameters;
    /** @var string */
    private $rel;
    /** @var string */
    private $collectionNodeName;
    /** @var string */
    private $nodeName;

    public function __construct(array $values)
    {

        if (!isset($values['route'])) {
            throw new InvalidArgumentException('The "route" attribute must be set.');
        }
        $this->route = $values['route'];

        if (isset($values['absolute'])) {
            $this->absolute = $values['absolute'];
        } else {
            $this->absolute = true;
        }

        if (isset($values['rel'])) {
            $this->rel = $values['rel'];
        }

        if (isset($values['collectionNodeName'])) {
            $this->collectionNodeName = $values['collectionNodeName'];
        }

        if (isset($values['nodeName'])) {
            $this->nodeName = $values['nodeName'];
        }

        if (isset($values['parameters'])) {
            $i = 0;
            foreach ($values['parameters'] as $value) {
                $i++;
                if (!isset($value['name'])) {
                    throw new InvalidArgumentException(sprintf('The "parameters[%s].name" attribute must be set.', $i));
                }
                if (!isset($value['type'])) {
                    $value['type'] = LinkMetadata::$DEFAULT_TYPE;
                }
                if (!isset($value['value'])) {
                    throw new InvalidArgumentException(sprintf('The "parameters[%s].value" attribute must be set.', $i));
                }

                if (!in_array($value['type'], LinkMetadata::$TYPES)) {
                    throw new InvalidArgumentException(sprintf('The %s in "parameters[%s].type" is of wrong type. Valid types are %s.', $value['type'], $i, implode(',', LinkMetadata::$TYPES)));
                }

                $this->parameters[$value['name']] = array(
                    'type' => $value['type'],
                    'value' => $value['value']
                );
            }
        }
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
        return $this->collectionNodeName;
    }

    /**
     * @return string
     */
    public function getNodeName()
    {
        return $this->nodeName;
    }
}
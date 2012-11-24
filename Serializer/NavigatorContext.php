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

/**
 * @author Adrien Brault <adrien.brault@gmail.com>
 */
class NavigatorContext
{
    /**
     * @var int
     */
    private $direction;

    /**
     * @var int
     */
    private $depth;

    /**
     * @var string|null
     */
    private $path;

    /**
     * @var null|object
     */
    private $object;

    public function __construct($direction, $depth, $path, $object = null)
    {
        $this->direction = $direction;
        $this->depth = $depth;
        $this->path = $path;
        $this->object = $object;
    }

    /**
     * @return int
     */
    public function getDepth()
    {
        return $this->depth;
    }

    /**
     * @return int
     */
    public function getDirection()
    {
        return $this->direction;
    }

    /**
     * @return null|object instance, provided during serialization but not deserialization
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @return string|null
     */
    public function getPath()
    {
        return $this->path;
    }
}

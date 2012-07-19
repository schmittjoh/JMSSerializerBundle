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

use JMS\SerializerBundle\Serializer\Naming\PropertyNamingStrategyInterface;

abstract class AbstractVisitor implements VisitorInterface
{
    protected $namingStrategy;
    protected $customHandlers;

    public function __construct(PropertyNamingStrategyInterface $namingStrategy, array $customHandlers)
    {
        $this->namingStrategy = $namingStrategy;
        $this->customHandlers = $customHandlers;
    }

    public function getNamingStrategy()
    {
        return $this->namingStrategy;
    }

    public function prepare($data)
    {
        return $data;
    }

    public function visitLink($data, $type)
    {
        throw new \RuntimeException('Visitor doesn\'t support visiting link data.');
    }
}
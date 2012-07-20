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

namespace JMS\SerializerBundle\Tests\Fixtures;

use JMS\SerializerBundle\Annotation\Link as ALink;
use JMS\SerializerBundle\Annotation\XmlRoot;
use JMS\SerializerBundle\Annotation\Exclude;

/**
 * @ALink(
 *      absolute=false,
 *      route="r1",
 *      rel="http://rels.kartoncek.si/rel1",
 *      parameters={
 *          {"name"="p1", "type"="property", "value"="prop1"},
 *          {"name"="p2", "type"="method", "value"="method1"},
 *          {"name"="p3", "type"="static", "value"="static1"}
 *      }
 * )
 *
 * @ALink(
 *      collectionNodeName="__links",
 *      nodeName="_link",
 *      route="r2",
 *      rel="http://rels.kartoncek.si/rel2",
 *      parameters={
 *          {"name"="p1", "type"="property", "value"="prop1"},
 *          {"name"="p2", "type"="method", "value"="method1"},
 *          {"name"="p3", "type"="static", "value"="42"}
 *      }
 * )
 */
class Link
{
    public $serialized;

    /**
     * @Exclude
     */
    public $prop1;
    /**
     * @Exclude
     */
    private $method1;

    public function __construct($prop1, $method1, $serialized)
    {
        $this->prop1 = $prop1;
        $this->method1 = $method1;
        $this->serialized = $serialized;
    }

    public function method1()
    {
        return $this->method1;
    }
}

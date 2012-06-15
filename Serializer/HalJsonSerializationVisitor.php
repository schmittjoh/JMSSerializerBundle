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

use JMS\SerializerBundle\Metadata\ClassMetadata;
use JMS\SerializerBundle\Metadata\PropertyMetadata;

class HalJsonSerializationVisitor extends JsonSerializationVisitor
{

    protected $defaultRootName = 'items';

    public function visitLink($data, $type)
    {
        $final = array('_links' => array());

        foreach ($data as $linkNodes) {
            foreach ($linkNodes as $links) {
                foreach ($links as $link) {
                    $rel = $link['rel'];
                    unset($link['rel']);
                    $newData = parent::visitArray($link, $type);
                    $final['_links'][$rel] = $newData;
                }
            }
        }

        $this->data = array_merge($this->data, $final);
    }

    public function visitProperty(PropertyMetadata $metadata, $data)
    {
        $v = (null === $metadata->getter ? $metadata->reflection->getValue($data)
                : $data->{$metadata->getter}());

        $v = $this->navigator->accept($v, null, $this);
        if (null === $v) {
            return;
        }

        if ($metadata->inline && is_array($v)) {
            $this->data = array_merge($this->data, $v);
        } else {
            $k = $this->namingStrategy->translateName($metadata);
            if ($metadata->xmlCollection || $metadata->xmlCollectionInline) {
                $this->data['_embedded'][$k] = $v;
            } else {
                $this->data[$k] = $v;
            }
        }
    }

    public function visitTraversable(ClassMetadata $metadata, $data, $type)
    {
        $rs = parent::visitTraversable($metadata, $data, $type);
        $node = $metadata->xmlRootName ?: $this->defaultRootName;

        //traversable as a property of an object?
        if (($this->root instanceof \stdClass) || $this->dataStack->count() > 0) {
            return $rs;
        } else {
            $this->root = array('_embedded' => array($node => $this->root));
            return array('_embedded' => array($node => $rs));
        }
    }

}
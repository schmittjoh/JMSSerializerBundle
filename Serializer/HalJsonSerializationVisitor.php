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

use JMS\SerializerBundle\Metadata\PropertyMetadata;

class HalJsonSerializationVisitor extends JsonSerializationVisitor
{

    public function visitLink($data, $type)
    {
        $final = array('__links' => array());

        foreach ($data as $linkNodes) {
            foreach ($linkNodes as $links) {
                foreach ($links as $link) {
                    $rel = $link['rel'];
                    unset($link['rel']);
                    $newData = parent::visitArray($link, $type);
                    $final['__links'][$rel] = $newData;
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
}
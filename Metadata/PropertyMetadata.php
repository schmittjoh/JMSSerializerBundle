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

use Metadata\PropertyMetadata as BasePropertyMetadata;

class PropertyMetadata extends BasePropertyMetadata
{
    const ACCESS_TYPE_PROPERTY        = 'property';
    const ACCESS_TYPE_PUBLIC_METHOD   = 'public_method';

    public $sinceVersion;
    public $untilVersion;
    public $serializedName;
    public $type;
    public $xmlCollection = false;
    public $xmlCollectionInline = false;
    public $xmlEntryName;
    public $xmlKeyAttribute;
    public $xmlAttribute = false;
    public $xmlValue = false;
    public $getter;
    public $setter;
    public $inline = false;
    public $readOnly = false;

    public function setAccessor($type, $getter = null, $setter = null)
    {
        if (self::ACCESS_TYPE_PUBLIC_METHOD === $type) {
            $class = $this->reflection->getDeclaringClass();

            if (empty($getter)) {
                if ($class->hasMethod('get'.$this->name) && $class->getMethod('get'.$this->name)->isPublic()) {
                    $getter = 'get'.$this->name;
                } else if ($class->hasMethod('is'.$this->name) && $class->getMethod('is'.$this->name)->isPublic()) {
                    $getter = 'is'.$this->name;
                } else {
                    throw new \RuntimeException(sprintf('There is neither a public %s method, nor a public %s method in class %s. Please specify which public method should be used for retrieving the value of the property %s.', 'get'.ucfirst($this->name), 'is'.ucfirst($this->name), $this->class, $this->name));
                }
            }

            if (empty($setter)) {
                if ($class->hasMethod('set'.$this->name) && $class->getMethod('set'.$this->name)->isPublic()) {
                    $setter = 'set'.$this->name;
                } else {
                    throw new \RuntimeException(sprintf('There is no public %s method in class %s. Please specify which public method should be used for setting the value of the property %s.', 'set'.ucfirst($this->name), $this->class, $this->name));
                }
            }
        }

        $this->getter = $getter;
        $this->setter = $setter;
    }

    public function serialize()
    {
        return serialize(array(
            $this->sinceVersion,
            $this->untilVersion,
            $this->serializedName,
            $this->type,
            $this->xmlCollection,
            $this->xmlCollectionInline,
            $this->xmlEntryName,
            $this->xmlKeyAttribute,
            $this->xmlAttribute,
            $this->xmlValue,
            $this->getter,
            $this->setter,
            $this->inline,
            $this->readOnly,
            parent::serialize(),
        ));
    }

    public function unserialize($str)
    {
        list(
            $this->sinceVersion,
            $this->untilVersion,
            $this->serializedName,
            $this->type,
            $this->xmlCollection,
            $this->xmlCollectionInline,
            $this->xmlEntryName,
            $this->xmlKeyAttribute,
            $this->xmlAttribute,
            $this->xmlValue,
            $this->getter,
            $this->setter,
            $this->inline,
            $this->readOnly,
            $parentStr
        ) = unserialize($str);

        parent::unserialize($parentStr);
    }
}
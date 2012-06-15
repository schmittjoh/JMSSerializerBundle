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

use JMS\SerializerBundle\Annotation\VirtualProperty;
use JMS\SerializerBundle\Annotation\SerializedName;

/**
 * An array-acting object that holds many log instances.
 */
class LogList implements \IteratorAggregate, \Countable, \ArrayAccess
{
    protected $logs = array();

    /**
     * @param Log $log
     */
    public function add(Log $log)
    {
        $this->logs[] = $log;
    }

    /**
     * @see IteratorAggregate
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->logs);
    }

    /**
     * @see Countable
     */
    public function count()
    {
        return count($this->logs);
    }

    /**
     * @see ArrayAccess
     */
    public function offsetExists($offset)
    {
        return isset($this->logs[$offset]);
    }

    /**
     * @see ArrayAccess
     */
    public function offsetGet($offset)
    {
        return isset($this->logs[$offset]) ? $this->logs[$offset] : null;
    }

    /**
     * @see ArrayAccess
     */
    public function offsetSet($offset, $value)
    {
        if (null === $offset) {
            $this->logs[] = $value;
        } else {
            $this->logs[$offset] = $value;
        }
    }

    /**
     * @see ArrayAccess
     */
    public function offsetUnset($offset)
    {
        unset($this->logs[$offset]);
    }

}

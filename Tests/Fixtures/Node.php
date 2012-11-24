<?php

namespace JMS\SerializerBundle\Tests\Fixtures;

use JMS\SerializerBundle\Annotation as Serializer;

class Node
{
    /**
     * @Serializer\MaxDepth(3)
     */
    public $children;
    public $depth;

    public function __construct($children = array())
    {
        if (is_array($children)) {
            $children = array($children);
        }

        $this->children = $children;
    }
}

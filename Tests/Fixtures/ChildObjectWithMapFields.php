<?php

namespace JMS\SerializerBundle\Tests\Fixtures;

use JMS\SerializerBundle\Annotation\AccessorOrder;
use JMS\SerializerBundle\Annotation\Type;
use JMS\SerializerBundle\Annotation\VirtualProperty;
use JMS\SerializerBundle\Annotation\SerializedName;
use JMS\SerializerBundle\Annotation\Exclude;
use JMS\SerializerBundle\Annotation\MapFields;

class ChildObjectWithMapFields
{

    /**
     * @Type("JMS\SerializerBundle\Tests\Fixtures\Comment")
     * @SerializedName("Comment")
     * @MapFields({"author"})
     */
    protected $parent;

    public function __construct(Comment $comment)
    {
        $this->parent = $comment;
    }

    public function getParent()
    {
        return $this->parent;
    }
}
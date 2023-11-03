<?php

declare(strict_types=1);

namespace JMS\SerializerBundle\Tests\DependencyInjection\Fixture;

use JMS\Serializer\Annotation as Serializer;

/** @Serializer\VirtualProperty(exp="object.getName()", name="v_prop_name") */
#[Serializer\VirtualProperty(name: 'v_prop_name', exp: 'object.getName()')]
class ObjectUsingExpressionProperties
{
    /** @Serializer\Exclude */
    #[Serializer\Exclude]
    private $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }
}

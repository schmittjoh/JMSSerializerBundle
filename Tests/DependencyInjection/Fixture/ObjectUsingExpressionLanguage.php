<?php

declare(strict_types=1);

namespace JMS\SerializerBundle\Tests\DependencyInjection\Fixture;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\VirtualProperty(exp="parameter('foo')", name="virtual")
 */
#[Serializer\VirtualProperty(name: 'virtual', exp: 'parameter("foo")')]
class ObjectUsingExpressionLanguage
{
    /**
     * @Serializer\Expose(if="object.isAllowed()")
     */
    #[Serializer\Expose(if: 'object.isAllowed()')]
    private $name;

    /**
     * @Serializer\Exclude()
     */
    #[Serializer\Exclude]
    private $isAllowed;

    public function __construct($name, $isAllowed)
    {
        $this->name = $name;
        $this->isAllowed = $isAllowed;
    }

    public function isAllowed()
    {
        return $this->isAllowed;
    }
}

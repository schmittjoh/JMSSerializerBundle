<?php

declare(strict_types=1);

namespace JMS\SerializerBundle\Tests\DependencyInjection\Fixture\IncludeInterfaces;

use JMS\Serializer\Annotation\Type;

class AnObject
{
    /**
     * @Type("string")
     */
    private $foo;

    /**
     * @Type("JMS\SerializerBundle\Tests\DependencyInjection\Fixture\IncludeInterfaces\AnInterface")
     */
    private $bar;

    public function __construct(string $foo, AnInterface $bar)
    {
        $this->foo = $foo;
        $this->bar = $bar;
    }
}

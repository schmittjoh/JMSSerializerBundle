<?php

declare(strict_types=1);

namespace JMS\SerializerBundle\Tests\DependencyInjection\Fixture\IncludeInterfaces;

use JMS\Serializer\Annotation\Type;

class AnInterfaceImplementation implements AnInterface
{
    /**
     * @Type("string")
     */
    private $bar;

    public function __construct(string $bar)
    {
        $this->bar = $bar;
    }

    public function execute(): void
    {
        $this->bar = 'overwrite';
    }
}

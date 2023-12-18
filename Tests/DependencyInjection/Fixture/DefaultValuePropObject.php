<?php

declare(strict_types=1);

namespace JMS\SerializerBundle\Tests\DependencyInjection\Fixture;

class DefaultValuePropObject
{
    private string $foo = 'some-value';

    public function __construct(private string $boo = 'another-value')
    {
    }
}

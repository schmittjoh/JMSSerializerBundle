<?php

declare(strict_types=1);

namespace JMS\SerializerBundle\Tests\DependencyInjection\Fixture;

class ObjectUsingEnum
{
    private Card $one;
    private $two;
    private array $three;

    public function __construct()
    {
        $this->one = Card::Black;
        $this->two = Card::Red;
        $this->three = [Card::Red, Card::Black];
    }
}

enum Card
{
    case Black;
    case Red;
}

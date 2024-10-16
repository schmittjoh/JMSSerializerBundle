<?php

declare(strict_types=1);

namespace JMS\SerializerBundle\Tests\DependencyInjection\Fixture;

use JMS\Serializer\Annotation\Type;

class ObjectUsingEnumDeserialize
{
    private ObjectUsingEnumDeserializeCard $one;

    /** @Type("enum<JMS\SerializerBundle\Tests\DependencyInjection\Fixture\ObjectUsingEnumDeserializeCard>") */
    #[Type(name: 'enum<JMS\SerializerBundle\Tests\DependencyInjection\Fixture\ObjectUsingEnumDeserializeCard>')]
    private $two;
    /** @Type("array<enum<JMS\SerializerBundle\Tests\DependencyInjection\Fixture\ObjectUsingEnumDeserializeCard>>") */
    #[Type(name: 'array<enum<JMS\SerializerBundle\Tests\DependencyInjection\Fixture\ObjectUsingEnumDeserializeCard>>')]
    private array $three;

    public function __construct()
    {
        $this->one = ObjectUsingEnumDeserializeCard::Black;
        $this->two = ObjectUsingEnumDeserializeCard::Red;
        $this->three = [ObjectUsingEnumDeserializeCard::Red, ObjectUsingEnumDeserializeCard::Black];
    }
}

enum ObjectUsingEnumDeserializeCard
{
    case Black;
    case Red;
}

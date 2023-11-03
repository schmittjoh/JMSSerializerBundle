<?php

declare(strict_types=1);

namespace JMS\SerializerBundle\Tests\DependencyInjection\Fixture;

use JMS\Serializer\Annotation\Type;

class AnotherSimpleObject
{
    /** @Type("float") */
    #[Type(name: 'float')]
    private $num;

    /** @Type("string") */
    #[Type(name: 'string')]
    private $str;

    /** @Type("string") */
    #[Type(name: 'string')]
    protected $camelCase;

    /** @Type("DateTime<'Y-m-d'>") */
    #[Type(name: 'DateTime<"Y-m-d">')]
    protected $date;

    public function __construct($num, $str, $camelCase)
    {
        $this->num = $num;
        $this->str = $str;
        $this->camelCase = $camelCase;
        $this->date = new \DateTime('2020-01-01');
    }
}

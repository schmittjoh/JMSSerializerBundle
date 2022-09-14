<?php

declare(strict_types=1);

namespace JMS\SerializerBundle\Tests\DependencyInjection\Fixture;

use JMS\Serializer\Annotation\Type;

class AnotherSimpleObject
{
    /** @Type("float") */
    private $num;

    /**
     * @Type("string")
     */
    private $str;

    /** @Type("string") */
    protected $camelCase;

    /** @Type("DateTime<'Y-m-d'>") */
    protected $date;

    public function __construct($num, $str, $camelCase)
    {
        $this->num = $num;
        $this->str = $str;
        $this->camelCase = $camelCase;
        $this->date = new \DateTime('2020-01-01');
    }
}

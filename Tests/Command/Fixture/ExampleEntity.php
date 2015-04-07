<?php
namespace JMS\SerializerBundle\Tests\Command\Fixture;

use JMS\Serializer\Annotation\Groups;

class ExampleEntity {

    /**
     * @Groups({"TestGroup1"})
     */
    public $property;

    /**
     * @Groups({"TestGroup2", "TestGroup3"})
     */
    public $otherProperty;

    /**
     * @Groups({"TestGroup2"})
     */
    public $anotherProperty;
}

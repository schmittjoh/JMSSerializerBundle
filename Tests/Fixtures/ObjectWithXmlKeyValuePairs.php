<?php

namespace JMS\SerializerBundle\Tests\Fixtures;

use JMS\SerializerBundle\Annotation\XmlKeyValuePairs;

class ObjectWithXmlKeyValuePairs
{
    /**
     * @var array
     * @XmlKeyValuePairs
     */
    private $array = array(
        'key-one' => 'foo',
        'key-two' => 1,
        'nested-array' => array(
            'bar' => 'foo',
        ),
        'without-keys' => array(
            1,
            'test'
        ),
        'mixed' => array(
            'test',
            'foo' => 'bar',
            '1_foo' => 'bar'
        ),
        1 => 'foo'
    );
}

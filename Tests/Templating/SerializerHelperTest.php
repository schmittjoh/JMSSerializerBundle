<?php

declare(strict_types=1);

namespace JMS\SerializerBundle\Tests\Templating;

use JMS\SerializerBundle\Templating\SerializerHelper;
use PHPUnit\Framework\TestCase;

class SerializerHelperTest extends TestCase
{
    public function testTemplatingProxiesToTheSerializer()
    {
        $serializer = $this->getMockBuilder('JMS\Serializer\SerializerInterface')->getMock();
        $serializer
            ->expects($this->once())
            ->method('serialize')
            ->will($this->returnValue('serialized'));

        $helper = new SerializerHelper($serializer);

        $this->assertEquals('serialized', $helper->serialize(new \stdClass()));
    }
}

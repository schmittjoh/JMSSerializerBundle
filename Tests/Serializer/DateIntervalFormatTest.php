<?php

namespace JMS\SerializerBundle\Tests\Serializer;

use JMS\SerializerBundle\Serializer\Handler\DateIntervalHandler;

class DateIntervalFormatTest extends \PHPUnit_Framework_TestCase
{
    public function testFormat()
    {
        $dtf = new DateIntervalHandler();

        $iso8601DateIntervalString = $dtf->format(new \DateInterval('PT45M'));

        $this->assertEquals($iso8601DateIntervalString, 'PT45M');

        $iso8601DateIntervalString = $dtf->format(new \DateInterval('P2YT45M'));

        $this->assertEquals($iso8601DateIntervalString, 'P2YT45M');

        $iso8601DateIntervalString = $dtf->format(new \DateInterval('P2Y4DT6H8M16S'));

        $this->assertEquals($iso8601DateIntervalString, 'P2Y4DT6H8M16S');
    }
}

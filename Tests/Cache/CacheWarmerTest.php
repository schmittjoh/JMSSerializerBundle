<?php

namespace JMS\SerializerBundle\Tests\Cache;

use JMS\SerializerBundle\Cache\CacheWarmer;
use JMS\SerializerBundle\Tests\Cache\Files\Bar\BarBar;
use Metadata\MetadataFactoryInterface;
use PHPUnit\Framework\TestCase;

class CacheWarmerTest extends TestCase
{
    private $metadataFactory;

    public function setUp()
    {
        $this->metadataFactory = $this->getMockBuilder(MetadataFactoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testWarmUpRecursive()
    {

        $this->metadataFactory->expects($this->exactly(3))
            ->method('getMetadataForClass');

        $warmer = new CacheWarmer([__DIR__ . "/Files"], $this->metadataFactory);
        $warmer->warmUp("foo");
    }

    public function testWarmUpRecursiveWithInclusion()
    {
        $this->metadataFactory->expects($this->exactly(1))
            ->method('getMetadataForClass')->with(BarBar::class);

        $warmer = new CacheWarmer([__DIR__ . "/Files/Ba*"], $this->metadataFactory);
        $warmer->warmUp("foo");
    }

    public function testWarmUpRecursiveWithExclusion()
    {
        $this->metadataFactory->expects($this->exactly(2))
            ->method('getMetadataForClass');

        $warmer = new CacheWarmer([__DIR__ . "/Files"], $this->metadataFactory, ["Bar"]);
        $warmer->warmUp("foo");
    }
}


<?php

/*
 * Copyright 2011 Johannes M. Schmitt <schmittjoh@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

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


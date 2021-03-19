<?php

declare(strict_types=1);

namespace JMS\SerializerBundle\Tests\Cache;

use JMS\SerializerBundle\Cache\CacheClearer;
use Metadata\Cache\PsrCacheAdapter;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;

class CacheClearerTest extends TestCase
{
    public function testClear(): void
    {
        $cache = $this->createMock(CacheItemPoolInterface::class);
        $cache
            ->expects(self::once())
            ->method('clear')
            ->willReturn(true);

        $adapter = new PsrCacheAdapter('', $cache);
        $clearer = new CacheClearer($adapter);
        $clearer->clear('');
    }
}

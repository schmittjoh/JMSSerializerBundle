<?php

declare(strict_types=1);

namespace JMS\SerializerBundle\Tests\Cache;

use JMS\SerializerBundle\Cache\CacheClearer;
use Metadata\Cache\PsrCacheAdapter;
use PHPUnit\Framework\TestCase;

class CacheClearerTest extends TestCase
{
    public function testClear(): void
    {
        $adapter = $this->createMock(PsrCacheAdapter::class);
        $adapter
            ->expects(self::once())
            ->method('clear')
            ->willReturn(true);

        $clearer = new CacheClearer($adapter);
        $clearer->clear('');
    }
}

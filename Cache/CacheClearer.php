<?php

declare(strict_types=1);

namespace JMS\SerializerBundle\Cache;

use Metadata\Cache\CacheInterface;
use Metadata\Cache\ClearableCacheInterface;
use Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface;

/**
 * @author Alexander Strizhak <gam6itko@gmail.com>
 */
class CacheClearer implements CacheClearerInterface
{
    /**
     * @var CacheInterface|ClearableCacheInterface
     */
    private $cache;

    /**
     * @param CacheInterface|ClearableCacheInterface $cache
     */
    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    public function clear($cacheDir): void
    {
        if ($this->cache instanceof ClearableCacheInterface) {
            $this->cache->clear();
        }
    }
}

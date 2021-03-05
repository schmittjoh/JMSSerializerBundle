<?php

declare(strict_types=1);

namespace JMS\SerializerBundle\Cache;

use Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface;

class CacheClearer implements CacheClearerInterface
{
    /**
     * @var ClearableCacheInterface
     */
    private $cache;

    /**
     * @param ClearableCacheInterface $cache
     */
    public function __construct($cache)
    {
        $this->cache = $cache;
    }

    public function clear(string $cacheDirectory): void
    {
        if (method_exists($this->cache, 'clear')) { // $this->cache instanceof ClearableCacheInterface
            call_user_func([$this->cache, 'clear']); // $this->cache->clear();
        }
    }
}

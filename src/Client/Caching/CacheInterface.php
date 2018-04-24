<?php

namespace Weglot\Client\Caching;

use Psr\Cache\CacheItemPoolInterface;

/**
 * Interface CacheInterface
 * @package Weglot\Client\Caching
 */
interface CacheInterface
{
    /**
     * @param null|CacheItemPoolInterface $cacheItemPool
     * @return $this
     */
    public function setCacheItemPool($cacheItemPool);

    /**
     * @return CacheItemPoolInterface
     */
    public function getCacheItemPool();

    /**
     * @param int $cacheExpire  Time in seconds before expire, default is 86400
     * @return $this
     */
    public function setCacheExpire($cacheExpire);

    /**
     * @return int
     */
    public function getCacheExpire();

    /**
     * Check if cache is enabled
     *
     * @return bool
     */
    public function cacheEnabled();

    /**
     * Generate cache key based on sha1 hash
     *
     * @param array $body       Body content of the request as array
     * @return string
     */
    public function getCacheGenerateKey(array $body);

    /**
     * Confirms if the cache contains specified cache item
     *
     * @param string $key
     * @return bool
     */
    public function cacheHasItem($key);

    /**
     * @param string $key
     * @return mixed
     */
    public function cacheGetItem($key);

    /**
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public function cacheCommitItem($key, $value);
}

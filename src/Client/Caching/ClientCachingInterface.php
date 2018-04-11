<?php
/**
 * Created by PhpStorm.
 * User: bleduc
 * Date: 11/04/2018
 * Time: 10:11
 */

namespace Weglot\Client\Caching;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

interface ClientCachingInterface
{
    /**
     * @param CacheItemPoolInterface $cacheItemPool
     * @return $this
     */
    public function setCacheItemPool(CacheItemPoolInterface $cacheItemPool);

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
     * @param string $method    Method to use for given endpoint
     * @param string $endpoint  Endpoint to hit on API
     * @param array $body       Body content of the request as array
     * @return string
     */
    public function getCacheGenerateKey($method, $endpoint, array $body);

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

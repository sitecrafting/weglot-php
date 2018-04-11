<?php

namespace Weglot\Client\Caching;

use Psr\Cache\CacheItemPoolInterface;

/**
 * Trait ClientCachingTrait
 * @package Weglot\Client\Caching
 */
trait ClientCachingTrait
{
    /**
     * @var CacheItemPoolInterface
     */
    protected $cacheItemPool = null;

    /**
     * @var int
     */
    protected $cacheExpire = 86400;

    /**
     * {@inheritdoc}
     */
    public function setCacheItemPool(CacheItemPoolInterface $cacheItemPool)
    {
        $this->cacheItemPool = $cacheItemPool;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheItemPool()
    {
        return $this->cacheItemPool;
    }

    /**
     * {@inheritdoc}
     */
    public function setCacheExpire($cacheExpire)
    {
        $this->cacheExpire = $cacheExpire;
        
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheExpire()
    {
        return $this->cacheExpire;
    }

    /**
     * {@inheritdoc}
     */
    public function cacheEnabled()
    {
        return $this->cacheItemPool !== null;
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheGenerateKey($method, $endpoint, array $body)
    {
        $body['method'] = $method;
        $body['endpoint'] = $endpoint;

        return 'wg_' .sha1(json_encode($body));
    }

    /**
     * {@inheritdoc}
     */
    public function cacheHasItem($key)
    {
        return $this->cacheItemPool->hasItem($key);
    }

    /**
     * {@inheritdoc}
     */
    public function cacheGetItem($key)
    {
        return $this->cacheItemPool->getItem($key)->get();
    }

    /**
     * {@inheritdoc}
     */
    public function cacheCommitItem($key, $value)
    {
        $item = $this->cacheItemPool->getItem($key);
        $item->set($value);
        $item->expiresAfter($this->getCacheExpire());

        return $this->cacheItemPool->save($item);
    }
}

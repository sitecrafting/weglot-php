<?php

use Weglot\Client\Client;
use Predis\Client as Redis;
use Cache\Adapter\Predis\PredisCachePool;

class ClientCachingTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var \Weglot\Client\Client
     */
    protected $client;

    /**
     * @var Redis
     */
    protected $redis;

    /**
     * Init client & redis-server
     */
    protected function _before()
    {
        $this->client = new Client(getenv('WG_API_KEY'));

        $this->redis = new Redis([
            'scheme' => getenv('REDIS_SCHEME'),
            'host'   => getenv('REDIS_HOST'),
            'port'   => getenv('REDIS_PORT'),
        ]);
        $this->redis->connect();

        $itemPool = new PredisCachePool($this->redis);
        $itemPool->clear();
        $this->client->setCacheItemPool($itemPool);
    }

    // tests
    public function testRedisConnection()
    {
        $this->assertTrue($this->redis->isConnected());
    }

    public function testItemPool()
    {
        $this->assertTrue($this->client->getCacheItemPool() instanceof PredisCachePool);
    }

    public function testExpire()
    {
        $this->assertEquals(86400, $this->client->getCacheExpire());

        $this->client->setCacheExpire(240);
        $this->assertEquals(240, $this->client->getCacheExpire());
    }

    public function testGenerateKey()
    {
        $cacheKey = $this->client->getCacheGenerateKey('GET', '/translate', []);
        $this->assertEquals('wg_55b05c964ee9dd0c93ad585f44eb30043e2b303c', $cacheKey);
    }

    public function testHasItem()
    {
        $key = 'hasItem';

        $this->assertFalse($this->client->cacheHasItem($key));
        $this->_setValueForKey($key);
        $this->assertTrue($this->client->cacheHasItem($key));
    }

    public function testGetItem()
    {
        $key = 'getItem';

        $this->assertNull($this->client->cacheGetItem($key));
        $this->_setValueForKey($key);
        $this->assertEquals('some value', $this->client->cacheGetItem($key));
    }

    public function testCommitItem()
    {
        $key = 'commitItem';

        $this->assertNull($this->client->cacheGetItem($key));
        $this->client->cacheCommitItem($key, 'some value');
        $this->assertEquals('some value', $this->client->cacheGetItem($key));
    }

    public function testMakeRequest()
    {
        // goes in cache
        $response = $this->client->makeRequest('GET', '/status', []);
        $this->assertEquals([], $response);

        // use cache
        $response = $this->client->makeRequest('GET', '/status', []);
        $this->assertEquals([], $response);
    }

    /**
     * @param $key
     * @param string $value
     * @throws \Psr\Cache\InvalidArgumentException
     */
    private function _setValueForKey($key, $value = 'some value')
    {
        $item = $this->client->getCacheItemPool()->getItem($key);
        $item->set($value);
        $this->client->getCacheItemPool()->save($item);
    }
}

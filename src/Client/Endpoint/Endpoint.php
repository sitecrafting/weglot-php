<?php

namespace Weglot\Client\Endpoint;

use Psr\Http\Message\ResponseInterface;
use Weglot\Client\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class Endpoint
 * @package Weglot\Client\Endpoint
 */
abstract class Endpoint
{
    const METHOD = 'GET';
    const ENDPOINT = '/';

    /**
     * @var Client
     */
    protected $client;

    /**
     * Endpoint constructor.
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->setClient($client);
    }

    /**
     * @param Client $client
     * @return void
     */
    public function setClient(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return self::ENDPOINT;
    }

    /**
     * Used to run endpoint onto given Client
     */
    abstract public function handle();

    /**
     * @param array $body       Content of your request
     * @param bool $ignoreCache To ignore cache, false by default
     * @param bool $asArray
     * @return array|ResponseInterface
     * @throws GuzzleException
     */
    protected function request(array $body = [], $ignoreCache = false, $asArray = true)
    {
        if ($ignoreCache) {
            $oldCacheItemPool = $this->getClient()->getCacheItemPool();
            $this->getClient()->setCacheItemPool(null);
        }

        $parentClass = get_called_class();
        $response = $this->getClient()->makeRequest($parentClass::METHOD, $parentClass::ENDPOINT, $body, $asArray);

        if ($ignoreCache) {
            $this->getClient()->setCacheItemPool($oldCacheItemPool);
        }

        return $response;
    }
}

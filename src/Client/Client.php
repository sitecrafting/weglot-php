<?php

namespace Weglot\Client;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use Weglot\Client\Caching\ClientCachingInterface;
use Weglot\Client\Caching\ClientCachingTrait;

/**
 * Class Client
 * @package Weglot\Client
 */
class Client implements ClientCachingInterface
{
    use ClientCachingTrait;

    /**
     * Library version
     *
     * @var string
     */
    const VERSION = '0.1-beta';

    /**
     * Weglot API Key
     *
     * @var string
     */
    protected $apiKey;

    /**
     * Options for client
     *
     * @var array
     */
    protected $options;

    /**
     * Http connector
     *
     * @var GuzzleClient
     */
    protected $connector;

    /**
     * Client constructor.
     * @param string    $apiKey     your Weglot API key
     * @param array     $options    an array of options, currently only "host" is implemented
     */
    public function __construct($apiKey, $options = [])
    {
        $this->apiKey = $apiKey;
        $this->setOptions($options);
    }

    /**
     * @return bool
     */
    public function apiKeyCheck()
    {
        return (strlen($this->apiKey) === 36);
    }

    /**
     * Creating Guzzle HTTP connector based on $options
     */
    protected function setupConnector()
    {
        $this->connector = new GuzzleClient([
            'base_uri' => $this->options['host'],
            'headers' => [
                'Content-Type' => 'application/json',
                'User-Agent' => $this->options['user-agent']
            ],
            'query' => [
                'api_key' => $this->apiKey
            ]
        ]);
    }

    /**
     * Default options values
     *
     * @return array
     */
    public function defaultOptions()
    {
        return [
            'host'  => 'https://api.weglot.com',
            'user-agent' => 'Weglot/' .self::VERSION
        ];
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param array $options
     */
    public function setOptions($options)
    {
        // merging default options with user options
        $this->options = array_merge($this->defaultOptions(), $options);

        // then loading / reloading http connector
        $this->setupConnector();
    }

    /**
     * @return GuzzleClient
     */
    public function getConnector()
    {
        return $this->connector;
    }

    /**
     * Make the API call and return the response.
     *
     * @param string $method    Method to use for given endpoint
     * @param string $endpoint  Endpoint to hit on API
     * @param array $body       Body content of the request as array
     * @param bool $asArray     To know if we return an array or ResponseInterface
     * @return array|ResponseInterface
     * @throws GuzzleException
     */
    public function makeRequest($method, $endpoint, $body = [], $asArray = true)
    {
        if ($this->cacheEnabled()) {
            $cacheKey = $this->getCacheGenerateKey($method, $endpoint, $body);

            if ($this->cacheHasItem($cacheKey)) {
                return $this->cacheGetItem($cacheKey);
            }
        }

        $response = $this->connector->request($method, $endpoint, [
            'json' => $body
        ]);
        $array = json_decode($response->getBody()->getContents(), true);

        if ($this->cacheEnabled()) {
            $this->cacheCommitItem($cacheKey, $array);
        }

        if ($asArray) {
            return $array;
        }
        return $response;
    }
}

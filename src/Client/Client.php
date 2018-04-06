<?php

/**
 * HTTP Client library
 *
 * @author  Baptiste Leduc <baptiste@weglot.com>
 * @license https://opensource.org/licenses/MIT The MIT License
 */

namespace Weglot\Client;

use GuzzleHttp\Client as GuzzleClient;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\GuzzleException;

class Client
{
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
     * Creating Guzzle HTTP connector based on $options
     */
    protected function setupConnector()
    {
        $this->connector = new GuzzleClient([
            'base_uri' => $this->options['host'],
            'headers' => ['Content-Type' => 'application/json'],
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
     * @param string $method    Method to use for given endpoint
     * @param string $endpoint  Endpoint to hit on API
     * @param array $body       Body content of the request as array
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function makeRequest($method, $endpoint, $body = [])
    {
        return $this->connector->request($method, $endpoint, [
            'json' => $body
        ]);
    }
}

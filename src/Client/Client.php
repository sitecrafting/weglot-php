<?php

/**
 * HTTP Client library
 *
 * @author  Baptiste Leduc <baptiste@weglot.com>
 * @license https://opensource.org/licenses/MIT The MIT License
 */

/**
 * Created by PhpStorm.
 * User: bleduc
 * Date: 06/04/2018
 * Time: 11:13
 */

namespace Weglot\Client;

use GuzzleHttp\Client as GuzzleClient;

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
     * @param $method
     * @param $endpoint
     * @param array $body
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function makeRequest($method, $endpoint, $body = [])
    {
        $this->connector->request($method, $endpoint, [
            'json' => $body
        ]);
    }
}

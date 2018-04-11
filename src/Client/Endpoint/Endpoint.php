<?php
/**
 * Created by PhpStorm.
 * User: bleduc
 * Date: 06/04/2018
 * Time: 16:28
 */

namespace Weglot\Client\Endpoint;

use Weglot\Client\Client;
use GuzzleHttp\Exception\GuzzleException;

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
     * @param array $body   Content of your request
     * @return array
     * @throws GuzzleException
     */
    protected function request($body)
    {
        $parentClass = get_called_class();
        return $this->client->makeRequest($parentClass::METHOD, $parentClass::ENDPOINT, $body);
    }
}

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
     * @param string $apiKey
     * @param array $options
     */
    public function __construct($apiKey, array $options = [])
    {
        // merging default options with user options
        $this->options = array_merge($this->defaultOptions(), $options);

        $this->connector = $this->setupConnector();
    }

    /**
     * Creating Guzzle HTTP connector based on $options
     *
     * @return GuzzleClient
     */
    protected function setupConnector()
    {
        return new GuzzleClient([
            'base_uri' => $this->options['host']
        ]);
    }

    /**
     * Default options values
     *
     * @return array
     */
    protected function defaultOptions()
    {
        return [
            'host'  => 'https://api.weglot.com',
        ];
    }
}

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

class Client
{
    /**
     * @var array
     */
    protected $options;

    /**
     * Client constructor.
     * @param string $apiKey
     * @param array $options
     */
    public function __construct($apiKey, array $options = [])
    {
        // merging default options with user options
        $this->options = array_merge($this->defaultOptions(), $options);
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
}

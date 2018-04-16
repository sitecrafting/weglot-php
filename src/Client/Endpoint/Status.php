<?php

namespace Weglot\Client\Endpoint;

use GuzzleHttp\Exception\GuzzleException;
use Weglot\Client\Api\Exception\ApiError;

/**
 * Class Status
 * @package Weglot\Client\Endpoint
 */
class Status extends Endpoint
{
    const METHOD = 'GET';
    const ENDPOINT = '/status';

    /**
     * @return bool
     * @throws ApiError
     */
    public function handle()
    {
        $response = $this->request([], true, false);

        if ($response->getStatusCode() === 200) {
            return true;
        }
        return false;
    }
}

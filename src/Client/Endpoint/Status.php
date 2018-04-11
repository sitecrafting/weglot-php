<?php
/**
 * Created by PhpStorm.
 * User: bleduc
 * Date: 06/04/2018
 * Time: 16:28
 */

namespace Weglot\Client\Endpoint;

use GuzzleHttp\Exception\GuzzleException;

class Status extends Endpoint
{
    const METHOD = 'GET';
    const ENDPOINT = '/status';

    /**
     * @return bool
     * @throws GuzzleException
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

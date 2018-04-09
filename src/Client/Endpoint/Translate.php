<?php
/**
 * Created by PhpStorm.
 * User: bleduc
 * Date: 06/04/2018
 * Time: 16:28
 */

namespace Weglot\Client\Endpoint;

use Weglot\Client\Api\Exception\MissingRequiredParamException;
use Weglot\Client\Api\TranslateEntry;
use Weglot\Client\Factory\Translate as TranslateFactory;
use GuzzleHttp\Exception\GuzzleException;

class Translate extends Endpoint
{
    const METHOD = 'POST';
    const ENDPOINT = '/translate';

    /**
     * @return TranslateEntry
     * @throws GuzzleException
     * @throws MissingRequiredParamException
     */
    public function handle()
    {
        $body = [
            'l_from' => 'en',
            'l_to' => 'de',
            'words' => [
                ['t' => 1, 'w' => 'This is a blue car'],
                ['t' => 1, 'w' => 'This is a black car'],
            ],
            'title' => 'Baptiste Leduc | Backend Developer',
            'request_url' => 'http://mealtime.io/'
        ];
        $response = $this->request($body);

        $factory = new TranslateFactory($response);
        return $factory->handle();
    }

    /**
     * @return bool
     */
    public function check()
    {
        return true;
    }
}

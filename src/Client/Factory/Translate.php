<?php
/**
 * Created by PhpStorm.
 * User: bleduc
 * Date: 09/04/2018
 * Time: 10:04
 */

namespace Weglot\Client\Factory;

use Psr\Http\Message\ResponseInterface;
use Weglot\Client\Api\Exception\InvalidWordTypeException;
use Weglot\Client\Api\Exception\MissingRequiredParamException;
use Weglot\Client\Api\TranslateEntry;
use Weglot\Client\Api\WordEntry;

class Translate
{
    /**
     * @var ResponseInterface
     */
    protected $response = null;

    /**
     * Translate constructor.
     * @param ResponseInterface $response
     */
    public function __construct(ResponseInterface $response)
    {
        $this->setResponse($response);
    }

    /**
     * @param ResponseInterface $response
     * @return $this
     */
    public function setResponse(ResponseInterface $response)
    {
        $this->response = $response;

        return $this;
    }

    /**
     * @return ResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @return array
     */
    public function getResponseAsArray()
    {
        return json_decode($this->response->getBody()->getContents(), true);
    }

    /**
     * @return TranslateEntry
     * @throws MissingRequiredParamException
     * @throws InvalidWordTypeException
     */
    public function handle()
    {
        $response = $this->getResponseAsArray();

        $params = [
            'language_from' => $response['l_from'],
            'language_to' => $response['l_to'],
            'bot' => $response['bot'],
            'request_url' => $response['request_url'],
            'title' => $response['title']
        ];
        $translate = new TranslateEntry($params);

        foreach ($response['from_words'] as $word) {
            $translate->getInputWords()->addOne(new WordEntry($word));
        }
        foreach ($response['to_words'] as $word) {
            $translate->getOutputWords()->addOne(new WordEntry($word));
        }

        return $translate;
    }
}

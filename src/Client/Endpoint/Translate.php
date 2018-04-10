<?php
/**
 * Created by PhpStorm.
 * User: bleduc
 * Date: 06/04/2018
 * Time: 16:28
 */

namespace Weglot\Client\Endpoint;

use Weglot\Client\Api\Exception\InputAndOutputCountMatchException;
use Weglot\Client\Api\Exception\InvalidWordTypeException;
use Weglot\Client\Api\Exception\MissingRequiredParamException;
use Weglot\Client\Api\Exception\MissingWordsOutputException;
use Weglot\Client\Api\TranslateEntry;
use Weglot\Client\Client;
use Weglot\Client\Factory\Translate as TranslateFactory;
use GuzzleHttp\Exception\GuzzleException;

class Translate extends Endpoint
{
    const METHOD = 'POST';
    const ENDPOINT = '/translate';

    /**
     * @var TranslateEntry
     */
    protected $translateEntry;

    /**
     * Translate constructor.
     * @param TranslateEntry $translateEntry
     * @param Client $client
     */
    public function __construct(TranslateEntry $translateEntry, Client $client)
    {
        $this->setTranslateEntry($translateEntry);
        parent::__construct($client);
    }

    /**
     * @return TranslateEntry
     */
    public function getTranslateEntry()
    {
        return $this->translateEntry;
    }

    /**
     * @param TranslateEntry $translateEntry
     * @return $this
     */
    public function setTranslateEntry(TranslateEntry $translateEntry)
    {
        $this->translateEntry = $translateEntry;

        return $this;
    }

    /**
     * @return TranslateEntry
     * @throws GuzzleException
     * @throws InvalidWordTypeException
     * @throws MissingRequiredParamException
     * @throws MissingWordsOutputException
     * @throws InputAndOutputCountMatchException
     */
    public function handle()
    {
        $asArray = $this->translateEntry->jsonSerialize();
        $response = $this->request($asArray);

        $factory = new TranslateFactory($response);
        return $factory->handle();
    }
}

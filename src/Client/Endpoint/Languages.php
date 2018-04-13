<?php

namespace Weglot\Client\Endpoint;

use Weglot\Client\Api\Exception\InvalidLanguageException;
use Weglot\Client\Client;
use Weglot\Client\Factory\Languages as LanguagesFactory;

/**
 * Class Languages
 * @package Weglot\Client\Endpoint
 */
class Languages extends Endpoint
{
    const METHOD = 'GET';
    const ENDPOINT = '/languages';

    /**
     * @var string
     */
    protected $iso639;

    /**
     * Languages constructor.
     * @param string $iso639    ISO 639-1 code to identify language
     * @param Client $client
     */
    public function __construct($iso639, Client $client)
    {
        $this->setIso639($iso639);
        parent::__construct($client);
    }

    /**
     * @param $iso639
     * @return $this
     */
    public function setIso639($iso639)
    {
        $this->iso639 = $iso639;

        return $this;
    }

    /**
     * @return string
     */
    public function getIso639()
    {
        return $this->iso_639_1;
    }

    /**
     * @return array
     * @throws InvalidLanguageException
     */
    public function handle()
    {
        $data = LanguagesFactory::data();
        if (!isset($data[$this->getIso639()])) {
            throw new InvalidLanguageException();
        }
        $language = $data[$this->getIso639()];

        $factory = new LanguagesFactory($language);
        return $factory->handle();
    }
}

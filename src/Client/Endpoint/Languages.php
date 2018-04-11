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
    protected $iso_639_1;

    /**
     * Languages constructor.
     * @param string $iso_639_1     ISO 639-1 code to identify language
     * @param Client $client
     */
    public function __construct($iso_639_1, Client $client)
    {
        $this->setIso639($iso_639_1);
        parent::__construct($client);
    }

    /**
     * @param $iso_639_1
     * @return $this
     */
    public function setIso639($iso_639_1)
    {
        $this->iso_639_1 = $iso_639_1;

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
        if (!isset($data[$this->iso_639_1])) {
            throw new InvalidLanguageException();
        }
        $language = $data[$this->iso_639_1];

        $factory = new LanguagesFactory($language);
        return $factory->handle();
    }
}

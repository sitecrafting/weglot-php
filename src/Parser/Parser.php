<?php

namespace Weglot\Parser;

use Weglot\Client\Client;
use Weglot\Parser\ConfigProvider\ConfigProviderInterface;

/**
 * Class Parser
 * @package Weglot\Parser
 */
class Parser implements ParserInterface
{
    /**
     * {@inheritdoc}
     */
    public function __construct(Client $client, ConfigProviderInterface $config, array $excludeBlocks = [])
    {
        //
    }

    /**
     * {@inheritdoc}
     */
    public function translate($source, $languageFrom, $languageTo)
    {
        return $source;
    }
}

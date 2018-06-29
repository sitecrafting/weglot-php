<?php

use Weglot\Client\Client;
use Weglot\Parser\ConfigProvider\ManualConfigProvider;
use Weglot\Client\Api\Enum\BotType;
use Weglot\Parser\Parser;
use Weglot\Parser\ConfigProvider\ConfigProviderInterface;

class AbstractParserCrawlerAfterEventTest extends \Codeception\Test\Unit
{
    /**
     * @var array
     */
    protected $sample;

    /**
     * @var Parser
     */
    protected $parser;

    protected function _before()
    {
        $this->sample = [
            'en' => file_get_contents(__DIR__ . '/../Resources/en-sample.html'),
            'fr' => file_get_contents(__DIR__ . '/../Resources/fr-sample.html'),
        ];

        $client = new Client(getenv('WG_API_KEY'));
        $config = new ManualConfigProvider('https://www.google.com/', BotType::HUMAN);

        $this->parserNew($client, $config);
    }

    protected function parserNew(Client $client, ConfigProviderInterface $config)
    {
        $this->parser = new Parser($client, $config);
    }
}
<?php

use Weglot\Client\Api\Enum\BotType;
use Weglot\Client\Client;
use Weglot\Parser\Parser;
use Weglot\Parser\ParserContext;
use Weglot\Parser\ConfigProvider\ConfigProviderInterface;
use Weglot\Parser\ConfigProvider\ManualConfigProvider;

abstract class AbstractEventTest extends \Codeception\Test\Unit
{
    /**
     * @var string
     */
    protected $url;

    /**
     * @var ConfigProviderInterface
     */
    protected $config;

    /**
     * @var Client
     */
    protected $client;

    protected function _before()
    {
        $this->url = 'https://weglot.com/documentation/getting-started';
        $this->config = new ManualConfigProvider($this->url, BotType::HUMAN);
        $this->client = new Client(getenv('WG_API_KEY'));
    }

    protected function _beforeDispatchEvent()
    {
        $parser = new Parser($this->client, $this->config);
        $sample = __DIR__ . '/../../Resources/en-sample.html';

        return [$parser, $sample];
    }

    protected function _defaultChecks(ParserContext $context, $languageFrom = 'en', $languageTo = 'fr')
    {
        // type
        $this->assertNotNull($context);
        $this->assertTrue($context instanceof ParserContext);

        // content
        $this->assertEquals($languageFrom, $context->getLanguageFrom());
        $this->assertEquals($languageTo, $context->getLanguageTo());
    }
}

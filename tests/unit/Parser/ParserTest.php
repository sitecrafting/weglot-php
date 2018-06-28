<?php

use Weglot\Parser\ConfigProvider\ServerConfigProvider;
use Weglot\Parser\ConfigProvider\ManualConfigProvider;
use Weglot\Client\Api\Enum\BotType;
use Weglot\Client\Client;
use Weglot\Parser\Parser;
use Weglot\Util\Site;
use Weglot\Parser\ParserContext;
use Weglot\Parser\Event\ParserCrawlerBeforeEvent;
use Weglot\Parser\Event\ParserCrawlerAfterEvent;
use Weglot\Parser\Event\ParserTranslatedEvent;
use Weglot\Parser\Event\ParserRenderEvent;
use Symfony\Component\DomCrawler\Crawler;
use Weglot\Client\Api\TranslateEntry;

class ParserTest extends \Codeception\Test\Unit
{
    /**
     * @var array
     */
    protected $url;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var Client
     */
    protected $client;


    protected function _before()
    {
        $this->url = [
            'source' => 'https://weglot.com/documentation/getting-started',
            'translated' => 'https://weglot.com/fr/documentation/getting-started'
        ];

        // Config with $_SERVER variables
        $_SERVER['SERVER_NAME'] = 'weglot.com';
        $_SERVER['REQUEST_URI'] = '/documentation/getting-started';
        $_SERVER['HTTPS'] = 'on';
        $_SERVER['SERVER_PROTOCOL'] = 'http//';
        $_SERVER['SERVER_PORT'] = 443;
        $_SERVER['HTTP_USER_AGENT'] = 'Google';

        // Config manually
        $this->config = [
            'manual'    => new ManualConfigProvider($this->url['source'], BotType::HUMAN),
            'server'    => new ServerConfigProvider()
        ];

        // Client
        $this->client = new Client(getenv('WG_API_KEY'));
    }

    public function testNoTranslate()
    {
        $this->assertEquals('data-wg-notranslate', Parser::ATTRIBUTE_NO_TRANSLATE);
    }

    public function testTranslateFromUrl()
    {
        $parser = new Parser($this->client, $this->config['server']);
        $source = Site::get($this->url['source']);

        $translated = $parser->translate($source, 'en', 'fr');
        $this->assertNotEquals($translated, $source);

        $source = Site::get($this->url['translated']);
        $similarity = similar_text($source, $translated) / strlen($source);
        $this->assertTrue($similarity >= 0.8);
    }

    public function testTranslateFromString()
    {
        $parser = new Parser($this->client, $this->config['server']);
        $sample = __DIR__ . '/../Resources/en-sample.html';

        $translated = $parser->translate($sample, 'en', 'fr');
        $this->assertNotEquals($translated, $sample);
    }

    public function testDispatchCrawlerBeforeEvent()
    {
        list($parser, $sample) = $this->_beforeDispatchEvent();

        $parser->addListener('parser.crawler.before', function ($event) {
            $this->assertTrue($event instanceof ParserCrawlerBeforeEvent);

            $context = $event->getContext();

            // type
            $this->assertTrue($event->getContext() !== null);
            $this->assertTrue($context instanceof ParserContext);

            // content
            $this->assertEquals('en', $context->getLanguageFrom());
            $this->assertEquals('fr', $context->getLanguageTo());
            $this->assertNull($context->getCrawler());
            $this->assertNull($context->getTranslateEntry());
        });

        // trigger event
        $parser->translate($sample, 'en', 'fr');
    }

    public function testDispatchCrawlerAfterEvent()
    {
        list($parser, $sample) = $this->_beforeDispatchEvent();

        $parser->addListener('parser.crawler.after', function ($event) {
            $this->assertTrue($event instanceof ParserCrawlerAfterEvent);

            $context = $event->getContext();

            // type
            $this->assertNotNull($event->getContext());
            $this->assertTrue($context instanceof ParserContext);

            // content
            $this->assertEquals('en', $context->getLanguageFrom());
            $this->assertEquals('fr', $context->getLanguageTo());
            $this->assertNotNull($context->getCrawler());
            $this->assertTrue($context->getCrawler() instanceof Crawler);
            $this->assertNotNull($context->getTranslateEntry());
            $this->assertTrue($context->getTranslateEntry() instanceof TranslateEntry);
        });

        // trigger event
        $parser->translate($sample, 'en', 'fr');
    }

    public function testDispatchTranslateEvent()
    {
        list($parser, $sample) = $this->_beforeDispatchEvent();

        $parser->addListener('parser.translated', function ($event) {
            $this->assertTrue($event instanceof ParserTranslatedEvent);

            $context = $event->getContext();

            // type
            $this->assertNotNull($event->getContext());
            $this->assertTrue($context instanceof ParserContext);

            // content
            $this->assertEquals('en', $context->getLanguageFrom());
            $this->assertEquals('fr', $context->getLanguageTo());
            $this->assertNotNull($context->getCrawler());
            $this->assertTrue($context->getCrawler() instanceof Crawler);
            $this->assertNotNull($context->getTranslateEntry());
            $this->assertTrue($context->getTranslateEntry() instanceof TranslateEntry);
            $this->assertEquals($context->getTranslateEntry()->getInputWords()->count(), $context->getTranslateEntry()->getOutputWords()->count());
        });

        // trigger event
        $parser->translate($sample, 'en', 'fr');
    }

    public function testDispatchRenderEvent()
    {
        list($parser, $sample) = $this->_beforeDispatchEvent();

        $parser->addListener('parser.render', function ($event) {
            $this->assertTrue($event instanceof ParserRenderEvent);

            $context = $event->getContext();

            // type
            $this->assertNotNull($event->getContext());
            $this->assertTrue($context instanceof ParserContext);

            // content
            $this->assertEquals('en', $context->getLanguageFrom());
            $this->assertEquals('fr', $context->getLanguageTo());
            $this->assertNull($context->getCrawler());
            $this->assertNotNull($context->getTranslateEntry());
            $this->assertTrue($context->getTranslateEntry() instanceof TranslateEntry);
            $this->assertEquals($context->getTranslateEntry()->getInputWords()->count(), $context->getTranslateEntry()->getOutputWords()->count());
        });

        // trigger event
        $parser->translate($sample, 'en', 'fr');
    }

    protected function _beforeDispatchEvent()
    {
        $parser = new Parser($this->client, $this->config['server']);
        $sample = __DIR__ . '/../Resources/en-sample.html';

        return [$parser, $sample];
    }
}

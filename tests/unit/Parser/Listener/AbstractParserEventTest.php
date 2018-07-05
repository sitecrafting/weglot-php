<?php

use Weglot\Client\Client;
use Weglot\Parser\ConfigProvider\ManualConfigProvider;
use Weglot\Client\Api\Enum\BotType;
use Weglot\Parser\Parser;
use Weglot\Parser\ConfigProvider\ConfigProviderInterface;
use Weglot\Parser\Event\AbstractEvent;

abstract class AbstractParserEventTest extends \Codeception\Test\Unit
{
    /**
     * @var array
     */
    protected $sample;

    /**
     * @var Parser
     */
    protected $parser;

    /**
     * @var string
     */
    const EVENT = '';

    /**
     * @var int
     */
    const EVENT_PRIORITY = -1;

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


    public function testBehavior()
    {
        $class = get_called_class();

        $this->parser->addListener($class::EVENT, [$this, 'listenerCallback'], $class::EVENT_PRIORITY);
        $translated = $this->parser->translate($this->sample['en'], 'en', 'fr');

        $this->checks($translated);
    }

    abstract public function listenerCallback(AbstractEvent $event);
    abstract public function checks($translated);
}

abstract class AbstractParserCrawlerAfterEventTest extends AbstractParserEventTest
{
    const EVENT = 'parser.crawler.after';
}

abstract class AbstractParserCrawlerBeforeEventTest extends AbstractParserEventTest
{
    const EVENT = 'parser.crawler.before';
}

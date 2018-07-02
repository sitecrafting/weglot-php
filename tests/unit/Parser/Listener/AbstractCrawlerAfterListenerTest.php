<?php

use Weglot\Parser\Listener\AbstractCrawlerAfterListener;
use Weglot\Parser\Parser;
use Weglot\Client\Client;
use Weglot\Parser\ConfigProvider\ManualConfigProvider;
use Weglot\Client\Api\Enum\BotType;
use Weglot\Parser\Exception\ParserCrawlerAfterListenerException;
use Weglot\Client\Api\Enum\WordType;

class NoXPathListener extends AbstractCrawlerAfterListener
{
    public function xpath()
    {
        return '';
    }
}

class InvalidXPathListener extends AbstractCrawlerAfterListener
{
    public function xpath()
    {
        return '//text()';
    }
}

class ElementNodeTypeExceptionListener extends AbstractCrawlerAfterListener
{
    public function xpath()
    {
        return '//text()/parent::*[not(ancestor-or-self::*[@' .Parser::ATTRIBUTE_NO_TRANSLATE. '])]';
    }
}

class ElementNodeCallbackExceptionListener extends AbstractCrawlerAfterListener
{
    public function xpath()
    {
        return '//text()/parent::*[not(ancestor-or-self::*[@' .Parser::ATTRIBUTE_NO_TRANSLATE. '])]';
    }

    public function type(\DOMNode $node)
    {
        return WordType::VALUE;
    }
}

class AbstractCrawlerAfterListenerTest extends \Codeception\Test\Unit
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
        $this->parser = new Parser($client, $config);
    }

    public function testXPathException()
    {
        try {
            $this->parser->addListener('parser.crawler.after', new NoXPathListener());
            $translated = $this->parser->translate($this->sample['en'], 'en', 'fr');
        } catch (ParserCrawlerAfterListenerException $e) {
            $this->assertEquals('XPath query is empty or doesn\'t exclude non-translable blocks.', $e->getMessage());
        }

        try {
            $this->parser->addListener('parser.crawler.after', new InvalidXPathListener());
            $translated = $this->parser->translate($this->sample['en'], 'en', 'fr');
        } catch (ParserCrawlerAfterListenerException $e) {
            $this->assertEquals('XPath query is empty or doesn\'t exclude non-translable blocks.', $e->getMessage());
        }
    }

    public function testNoWordTypeException()
    {
        try {
            $this->parser->addListener('parser.crawler.after', new ElementNodeTypeExceptionListener(), 5);
            $translated = $this->parser->translate($this->sample['en'], 'en', 'fr');
        } catch (ParserCrawlerAfterListenerException $e) {
            $this->assertEquals('No word type set for this kind of node.', $e->getMessage());
        }
    }

    public function testNoCallbackException()
    {
        try {
            $this->parser->addListener('parser.crawler.after', new ElementNodeCallbackExceptionListener(), 5);
            $translated = $this->parser->translate($this->sample['en'], 'en', 'fr');
        } catch (ParserCrawlerAfterListenerException $e) {
            $this->assertEquals('No callback behavior set for this node type.', $e->getMessage());
        }
    }
}
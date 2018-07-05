<?php

use Weglot\Parser\ParserContext;
use Weglot\Client\Client;
use Weglot\Parser\ConfigProvider\ManualConfigProvider;
use Weglot\Client\Api\Enum\BotType;
use Weglot\Parser\Parser;
use Weglot\Parser\Exception\ParserContextException;
use Symfony\Component\DomCrawler\Crawler;
use Weglot\Client\Api\TranslateEntry;
use Weglot\Client\Api\Enum\WordType;

class ParserContextTest extends \Codeception\Test\Unit
{
    /**
     * @var array
     */
    protected $sample;

    /**
     * @var ParserContext
     */
    protected $context;

    protected function _before()
    {
        $this->sample = [
            'en' => file_get_contents(__DIR__ . '/Resources/en-sample.html'),
            'fr' => file_get_contents(__DIR__ . '/Resources/fr-sample.html'),
        ];

        $client = new Client(getenv('WG_API_KEY'));
        $config = new ManualConfigProvider('https://www.google.com/', BotType::HUMAN);
        $parser = new Parser($client, $config);

        $this->context = new ParserContext($parser, 'en', 'fr', $this->sample['en']);
    }

    public function testDefaults()
    {
        $this->assertNotNull($this->context->getParser());
        $this->assertTrue($this->context->getParser() instanceof Parser);

        $this->assertEquals('en', $this->context->getLanguageFrom());
        $this->assertEquals('fr', $this->context->getLanguageTo());

        $this->assertEquals($this->sample['en'], $this->context->getSource());

        $this->context->setSource($this->sample['fr']);
        $this->assertEquals($this->sample['fr'], $this->context->getSource());
    }

    public function testGenerateTranslateEntry()
    {
        $crawler = new Crawler($this->sample['en']);
        $this->context->setCrawler($crawler);

        $this->assertTrue($this->context->getCrawler() instanceof Crawler);

        $translateEntry = $this->context->generateTranslateEntry();

        $this->assertTrue($translateEntry instanceof TranslateEntry);
        $this->assertEquals('en', $translateEntry->getParams('language_from'));
        $this->assertEquals('fr', $translateEntry->getParams('language_to'));
        $this->assertEquals(BotType::HUMAN, $translateEntry->getParams('bot'));
        $this->assertEquals('https://www.google.com/', $translateEntry->getParams('request_url'));
    }

    public function testTranslateMap()
    {
        $testWord = 'The car is blue';
        $translatedWord = 'La voiture est bleue';

        $crawler = new Crawler($this->sample['en']);
        $this->context->setCrawler($crawler);
        $this->context->generateTranslateEntry();

        $this->assertEquals([], $this->context->getTranslateMap());

        $this->context->addWord($testWord, function ($translated) use ($translatedWord) {
            $this->assertEquals($translatedWord, $translated);
        });

        $this->assertEquals([0 => function ($translated) use ($translatedWord) {
            $this->assertEquals($translatedWord, $translated);
        }], $this->context->getTranslateMap());

        $translateMap = $this->context->getTranslateMap();
        foreach ($translateMap as $index => $element) {
            $word = $this->context->getTranslateEntry()->getInputWords()->offsetGet($index);

            $this->assertEquals($testWord, $word->getWord());
            $this->assertEquals(WordType::TEXT, $word->getType());

            $element($translatedWord);
        }
    }

    public function testSetSourceParserContextException()
    {
        try {
            $crawler = new Crawler($this->sample['en']);
            $this->context->setCrawler($crawler);

            $this->context->setSource($this->sample['fr']);
        } catch (ParserContextException $e) {
            $this->assertTrue(true);
        }
    }

    public function testSetTranslateEntryParserContextException()
    {
        try {
            $params = [
                'language_from' => 'en',
                'language_to' => 'fr',
                'bot' => BotType::HUMAN,
                'request_url' => 'https://www.google.com/'
            ];

            $translateEntry = new TranslateEntry($params);
            $this->context->setTranslateEntry($translateEntry);
        } catch (ParserContextException $e) {
            $this->assertTrue(true);
        }
    }

    public function testGenerateTranslateEntryParserContextException()
    {
        try {
            $this->context->generateTranslateEntry();
        } catch (ParserContextException $e) {
            $this->assertTrue(true);
        }
    }
}

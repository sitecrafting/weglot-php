<?php

namespace Weglot\Parser;

use Symfony\Component\DomCrawler\Crawler;
use Weglot\Client\Api\Enum\WordType;
use Weglot\Client\Api\Exception\InvalidWordTypeException;
use Weglot\Client\Api\Exception\MissingRequiredParamException;
use Weglot\Client\Api\TranslateEntry;
use Weglot\Client\Api\WordEntry;
use Weglot\Parser\ConfigProvider\ServerConfigProvider;
use Weglot\Parser\Exception\ParserContextException;

/**
 * Class ParserContext
 * @package Weglot\Parser
 */
class ParserContext
{
    /**
     * @var Parser
     */
    protected $parser;

    /**
     * @var string
     */
    protected $languageFrom;

    /**
     * @var string
     */
    protected $languageTo;

    /**
     * @var string
     */
    protected $source = '';

    /**
     * @var null|Crawler
     */
    protected $crawler = null;

    /**
     * @var null|TranslateEntry
     */
    protected $translateEntry = null;

    /**
     * @var array
     */
    protected $translateMap = [];

    /**
     * ParserContext constructor.
     * @param Parser $parser
     * @param string $languageFrom
     * @param string $languageTo
     * @param string $source
     */
    public function __construct(Parser $parser, $languageFrom, $languageTo, $source)
    {
        $this->parser = $parser;
        $this->languageFrom = $languageFrom;
        $this->languageTo = $languageTo;
        $this->source = $source;
    }

    /**
     * @return Parser
     */
    public function getParser()
    {
        return $this->parser;
    }

    /**
     * @return string
     */
    public function getLanguageFrom()
    {
        return $this->languageFrom;
    }

    /**
     * @return string
     */
    public function getLanguageTo()
    {
        return $this->languageTo;
    }

    /**
     * @param string $source
     * @return $this
     *
     * @throws ParserContextException
     */
    public function setSource($source)
    {
        $crawler = $this->getCrawler();
        if (!is_null($crawler) && $crawler instanceof Crawler) {
            throw new ParserContextException('It is not allowed to update source when crawler is active.');
        }

        $this->source = $source;

        return $this;
    }

    /**
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param null|Crawler $crawler
     * @return $this
     */
    public function setCrawler($crawler)
    {
        $this->crawler = $crawler;

        return $this;
    }

    /**
     * @return null|Crawler
     */
    public function getCrawler()
    {
        return $this->crawler;
    }

    /**
     * @param TranslateEntry $translateEntry
     * @return $this
     *
     * @throws ParserContextException
     */
    public function setTranslateEntry(TranslateEntry $translateEntry)
    {
        if (is_null($this->translateEntry)) {
            throw new ParserContextException('TranslateEntry should be generated through `generateTranslateEntry()` function first.');
        }
        $this->translateEntry = $translateEntry;

        return $this;
    }

    /**
     * @return null|TranslateEntry
     */
    public function getTranslateEntry()
    {
        return $this->translateEntry;
    }

    /**
     * @param int $index
     * @param string $path
     * @return $this
     *
     * @throws ParserContextException
     */
    public function addToTranslateMap($index, $path)
    {
        if (in_array($path, array_values($this->translateMap))) {
            throw new ParserContextException('You can\'t have same DOM node path two times.');
        }
        $this->translateMap[$index] = $path;

        return $this;
    }

    /**
     * @return array
     */
    public function getTranslateMap()
    {
        return $this->translateMap;
    }

    /**
     * @return TranslateEntry
     *
     * @throws ParserContextException
     * @throws MissingRequiredParamException
     */
    public function generateTranslateEntry()
    {
        if (is_null($this->getCrawler())) {
            throw new ParserContextException('You can\'t generate translate entry without having a crawler initialized.');
        }

        $parameters = [
            'language_from' => $this->getLanguageFrom(),
            'language_to' => $this->getLanguageTo()
        ];

        // if data is coming from $_SERVER, load it ...
        if ($this->getParser()->getConfigProvider() instanceof ServerConfigProvider) {
            $this->getParser()->getConfigProvider()->loadFromServer();
        }

        // managing auto-discover for title
        if ($this->getParser()->getConfigProvider()->getAutoDiscoverTitle()) {
            $title = 'Empty title';

            foreach ($this->getCrawler()->filter('title') as $element) {
                if ($element->nodeValue != '') {
                    $title = $element->nodeValue;
                }
            }

            $parameters['title'] = $title;
        }

        $parameters = array_merge($parameters, $this->getParser()->getConfigProvider()->asArray());
        $this->translateEntry = new TranslateEntry($parameters);

        return $this->translateEntry;
    }

    /**
     * @param string $text
     * @param string $path
     * @param int $textType
     *
     * @throws InvalidWordTypeException
     * @throws ParserContextException
     */
    public function addWord($text, $path, $textType = WordType::TEXT)
    {
        $inputWords = $this->getTranslateEntry()->getInputWords();

        $index = count($inputWords);
        $inputWords->addOne(new WordEntry($text, $textType));
        $this->addToTranslateMap($index, $path);
    }
}

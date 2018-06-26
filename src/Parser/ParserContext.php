<?php

namespace Weglot\Parser;

use Symfony\Component\DomCrawler\Crawler;
use Weglot\Client\Api\TranslateEntry;
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
     * @return null|TranslateEntry
     */
    public function getTranslateEntry()
    {
        return $this->translateEntry;
    }

    /**
     * @return TranslateEntry
     *
     * @throws ParserContextException
     * @throws \Weglot\Client\Api\Exception\MissingRequiredParamException
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
}

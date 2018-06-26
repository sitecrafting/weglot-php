<?php

namespace Weglot\Parser;

use Symfony\Component\DomCrawler\Crawler;

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
    protected $source;

    /**
     * @var Crawler
     */
    protected $crawler;

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
     */
    public function setSource($source)
    {
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
     * @param Crawler $crawler
     * @return $this
     */
    public function setCrawler(Crawler $crawler)
    {
        $this->crawler = $crawler;

        return $this;
    }

    /**
     * @return Crawler
     */
    public function getCrawler()
    {
        return $this->crawler;
    }
}

<?php

namespace Weglot\Parser;

use Symfony\Component\DomCrawler\Crawler;
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
}

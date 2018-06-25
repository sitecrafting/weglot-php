<?php

namespace Weglot\Parser\Event;

use Symfony\Component\DomCrawler\Crawler;
use Weglot\Parser\Parser;

class ParserCrawlerAfterEvent extends AbstractEvent
{
    const NAME = 'parser.crawler.after';

    /**
     * @var Crawler
     */
    protected $crawler;

    /**
     * ParserCrawlerAfterEvent constructor.
     * @param Parser $parser
     * @param Crawler $crawler
     */
    public function __construct(Parser $parser, Crawler $crawler)
    {
        parent::__construct($parser);
        $this->crawler = $crawler;
    }

    /**
     * @return string
     */
    public function getCrawler()
    {
        return $this->crawler;
    }
}

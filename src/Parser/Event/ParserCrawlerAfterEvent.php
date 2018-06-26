<?php

namespace Weglot\Parser\Event;

use Symfony\Component\DomCrawler\Crawler;
use Weglot\Parser\ParserContext;

class ParserCrawlerAfterEvent extends AbstractEvent
{
    const NAME = 'parser.crawler.after';

    /**
     * @var Crawler
     */
    protected $crawler;

    /**
     * ParserCrawlerAfterEvent constructor.
     * @param ParserContext $context
     * @param Crawler $crawler
     */
    public function __construct(ParserContext $context, Crawler $crawler)
    {
        parent::__construct($context);
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

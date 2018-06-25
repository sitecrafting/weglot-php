<?php

namespace Weglot\Parser\Event;

use Weglot\Parser\Parser;

class ParserCrawlerBeforeEvent extends AbstractEvent
{
    const NAME = 'parser.crawler.before';

    /**
     * ParserInitEvent constructor.
     * @param Parser $parser
     */
    public function __construct(Parser $parser)
    {
        parent::__construct($parser);
    }
}

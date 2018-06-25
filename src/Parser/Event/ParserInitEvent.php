<?php

namespace Weglot\Parser\Event;

use Weglot\Parser\Parser;

class ParserInitEvent extends AbstractEvent
{
    const NAME = 'parser.init';

    /**
     * ParserInitEvent constructor.
     * @param Parser $parser
     */
    public function __construct(Parser $parser)
    {
        parent::__construct($parser);
    }
}

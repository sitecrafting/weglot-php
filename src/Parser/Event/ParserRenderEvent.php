<?php

namespace Weglot\Parser\Event;

use Weglot\Parser\Parser;

class ParserRenderEvent extends AbstractEvent
{
    const NAME = 'parser.render';

    /**
     * ParserInitEvent constructor.
     * @param Parser $parser
     */
    public function __construct(Parser $parser)
    {
        parent::__construct($parser);
    }
}

<?php

namespace Weglot\Parser\Event;

use Symfony\Component\EventDispatcher\Event;
use Weglot\Parser\Parser;

class ParserInitEvent extends Event
{
    const NAME = 'parser.init';

    /**
     * @var Parser
     */
    protected $parser;

    /**
     * ParserInitEvent constructor.
     * @param Parser $parser
     */
    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    /**
     * @return Parser
     */
    public function getParser()
    {
        return $this->parser;
    }
}

<?php

namespace Weglot\Parser\Event;

use Symfony\Component\EventDispatcher\Event;
use Weglot\Parser\Parser;

abstract class AbstractEvent extends Event
{
    /**
     * @var Parser
     */
    protected $parser;

    /**
     * AbstractEvent constructor.
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

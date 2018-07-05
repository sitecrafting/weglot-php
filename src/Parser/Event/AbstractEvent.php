<?php

namespace Weglot\Parser\Event;

use Symfony\Component\EventDispatcher\Event;
use Weglot\Parser\ParserContext;

abstract class AbstractEvent extends Event
{
    /**
     * @var ParserContext
     */
    protected $context;

    /**
     * AbstractEvent constructor.
     * @param ParserContext $context
     */
    public function __construct(ParserContext $context)
    {
        $this->context = $context;
    }

    /**
     * @return ParserContext
     */
    public function getContext()
    {
        return $this->context;
    }
}

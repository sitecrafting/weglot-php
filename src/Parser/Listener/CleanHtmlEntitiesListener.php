<?php

namespace Weglot\Parser\Listener;

use Weglot\Parser\Event\ParserRenderEvent;
use Weglot\Parser\Exception\ParserContextException;

class CleanHtmlEntitiesListener
{
    /**
     * @param ParserRenderEvent $event
     *
     * @throws ParserContextException
     */
    public function __invoke(ParserRenderEvent $event)
    {
        $source = $event->getContext()->getSource();

        $source = str_replace('&lt;', '<', $source);
        $source = str_replace('&gt;', '>', $source);

        $event->getContext()->setSource($source);
    }
}

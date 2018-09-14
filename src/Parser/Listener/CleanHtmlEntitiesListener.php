<?php

namespace Weglot\Parser\Listener;

use Weglot\Parser\Event\ParserRenderEvent;
use Weglot\Parser\Exception\ParserContextException;

final class CleanHtmlEntitiesListener
{
    /**
     * @param ParserRenderEvent $event
     *
     * @throws ParserContextException
     */
    public function __invoke(ParserRenderEvent $event)
    {
        $source = $event->getContext()->getSource();
        $source = str_replace('&amp;', '&', $source);
        $event->getContext()->setSource($source);
    }
}

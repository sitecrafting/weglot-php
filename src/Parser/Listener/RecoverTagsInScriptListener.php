<?php

namespace Weglot\Parser\Listener;

use Weglot\Parser\Event\ParserRenderEvent;
use Weglot\Parser\Exception\ParserContextException;

final class RecoverTagsInScriptListener
{
    /**
     * @param ParserRenderEvent $event
     *
     * @throws ParserContextException
     */
    public function __invoke(ParserRenderEvent $event)
    {
        $source = $event->getContext()->getSource();

        if (preg_match('#\<script(\s.*?)?\>(.*?)\<\/script\>#is', $source, $matches)) {
            $replacement = '<script' .$matches[1]. '>' .str_replace('\x3C', '<', $matches[2]). '</script>';
            $source = preg_replace('#\<script(\s.*?)?\>(.*?)\<\/script\>#is', $replacement, $source);
        }

        $event->getContext()->setSource($source);
    }
}

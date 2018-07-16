<?php

namespace Weglot\Parser\Listener;

use Weglot\Parser\Event\ParserCrawlerBeforeEvent;
use Weglot\Parser\Exception\ParserContextException;

final class ForgetTagsInScriptListener
{
    /**
     * @param ParserCrawlerBeforeEvent $event
     *
     * @throws ParserContextException
     */
    public function __invoke(ParserCrawlerBeforeEvent $event)
    {
        $source = $event->getContext()->getSource();
        $matches = [];

        if (preg_match('#\<script(\s.*?)?\>(.*?)\<\/script\>#is', $source, $matches)) {
            $replacement = '<script' .$matches[1]. '>' .str_replace('<', '\x3C', $matches[2]). '</script>';
            $source = preg_replace('#\<script(\s.*?)?\>(.*?)\<\/script\>#is', $replacement, $source);
        }

        $event->getContext()->setSource($source);
    }
}

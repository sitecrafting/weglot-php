<?php

namespace Weglot\Parser\Listener;

use Weglot\Parser\Event\ParserTranslatedEvent;

class DomReplaceListener
{
    /**
     * @param ParserTranslatedEvent $event
     */
    public function __invoke(ParserTranslatedEvent $event)
    {
        $replaceMap = $event->getContext()->getTranslateMap();
        $outputWords = $event->getContext()->getTranslateEntry()->getOutputWords();

        foreach ($replaceMap as $index => $callable) {
            $wordType = $outputWords[$index];
            $callable($wordType->getWord());
        }
    }
}

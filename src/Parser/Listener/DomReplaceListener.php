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
        $crawler = $event->getContext()->getCrawler();
        $replaceMap = $event->getContext()->getTranslateMap();
        $outputWords = $event->getContext()->getTranslateEntry()->getOutputWords();

        foreach ($replaceMap as $index => $details) {
            $wordType = $outputWords[$index];
            $property = $details['property'];

            $node = $crawler->filterXPath('/'.$details['path'])->getNode(0);
            $node->$property = $wordType->getWord();
        }
    }
}

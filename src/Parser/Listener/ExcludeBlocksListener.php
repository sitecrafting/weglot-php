<?php

namespace Weglot\Parser\Listener;

use Weglot\Parser\Event\ParserCrawlerAfterEvent;
use Weglot\Parser\Parser;

final class ExcludeBlocksListener
{
    /**
     * @param ParserCrawlerAfterEvent $event
     */
    public function __invoke(ParserCrawlerAfterEvent $event)
    {
        $crawler = $event->getContext()->getCrawler();
        $excludeBlocks = $event->getContext()->getParser()->getExcludeBlocks();

        foreach ($excludeBlocks as $exception)
        {
            $nodes = $crawler->filter($exception);
            foreach ($nodes as $node)
            {
                $node->setAttribute(Parser::ATTRIBUTE_NO_TRANSLATE, '');
            }
        }
    }
}

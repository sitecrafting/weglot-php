<?php

namespace Weglot\Parser\Listener;

use Weglot\Client\Api\Exception\InvalidWordTypeException;
use Weglot\Parser\Event\ParserCrawlerAfterEvent;
use Weglot\Parser\Exception\ParserContextException;
use Weglot\Parser\Parser;

class DomSpanListener
{
    /**
     * @param ParserCrawlerAfterEvent $event
     *
     * @throws InvalidWordTypeException
     * @throws ParserContextException
     */
    public function __invoke(ParserCrawlerAfterEvent $event)
    {
        $crawler = $event->getContext()->getCrawler();

        $nodes = $crawler->filterXPath('//span[not(ancestor-or-self::*[@' .Parser::ATTRIBUTE_NO_TRANSLATE. '])]/@title');
        foreach ($nodes as $node) {
            $value = trim($node->value);

            if ($value !== '') {
                $event->getContext()->addWord($value, $node->getNodePath(), function (\DOMAttr $node, $translated) {
                    $node->value = $translated;
                });
            }
        }
    }
}

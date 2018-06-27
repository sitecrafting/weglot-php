<?php

namespace Weglot\Parser\Listener;

use Weglot\Client\Api\Enum\WordType;
use Weglot\Client\Api\Exception\InvalidWordTypeException;
use Weglot\Parser\Event\ParserCrawlerAfterEvent;
use Weglot\Parser\Exception\ParserContextException;
use Weglot\Parser\Parser;

class DomIframeSrcListener
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

        $nodes = $crawler->filterXPath('//iframe[not(ancestor-or-self::*[@' .Parser::ATTRIBUTE_NO_TRANSLATE. '])]/@src');
        foreach ($nodes as $node) {
            $src = trim($node->nodeValue);
            if ($src !== '') {
                $event->getContext()->addWord($src, $node->getNodePath(), function (\DOMAttr $node, $translated) {
                    $node->nodeValue = $translated;
                }, WordType::IFRAME_SRC);
            }
        }
    }
}

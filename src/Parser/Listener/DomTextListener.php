<?php

namespace Weglot\Parser\Listener;

use Weglot\Client\Api\Exception\InvalidWordTypeException;
use Weglot\Parser\Event\ParserCrawlerAfterEvent;
use Weglot\Parser\Exception\ParserContextException;
use Weglot\Parser\Parser;

class DomTextListener
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

        $nodes = $crawler->filterXPath('//text()/parent::*[not(ancestor-or-self::*[@' .Parser::ATTRIBUTE_NO_TRANSLATE. '])]/text()');
        foreach ($nodes as $node) {
            $text = trim($node->textContent);
            $text = str_replace("\n", '', $text);
            $text = preg_replace('/\s+/', ' ', $text);

            if ($text !== '' && strpos($text, Parser::ATTRIBUTE_NO_TRANSLATE) === false) {
                $event->getContext()->addWord($text, $node->getNodePath(), function (\DOMText $node, $translated) {
                    $node->textContent = $translated;
                });
            }
        }
    }
}

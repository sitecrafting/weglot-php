<?php

namespace Weglot\Parser\Listener;

use Weglot\Client\Api\Enum\WordType;
use Weglot\Client\Api\Exception\InvalidWordTypeException;
use Weglot\Parser\Event\ParserCrawlerAfterEvent;
use Weglot\Parser\Exception\ParserContextException;
use Weglot\Parser\Parser;

class DomInputDataListener
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

        $nodes = $crawler->filterXPath('//input[(@type = \'submit\' or @type = \'button\') and not(ancestor-or-self::*[@' .Parser::ATTRIBUTE_NO_TRANSLATE. '])]/@*[name()=\'data-value\' or name()=\'data-order_button_text\']');
        foreach ($nodes as $node) {
            $text = trim($node->textContent);
            if ($text !== '') {
                $event->getContext()->addWord($text, $node->getNodePath(), function (\DOMAttr $node, $translated) {
                    $node->value = $translated;
                });
            }
        }
    }
}

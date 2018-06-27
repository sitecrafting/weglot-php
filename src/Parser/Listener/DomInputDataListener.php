<?php

namespace Weglot\Parser\Listener;

use Weglot\Client\Api\Exception\InvalidWordTypeException;
use Weglot\Parser\Event\ParserCrawlerAfterEvent;
use Weglot\Parser\Parser;

class DomInputDataListener
{
    /**
     * @param ParserCrawlerAfterEvent $event
     *
     * @throws InvalidWordTypeException
     */
    public function __invoke(ParserCrawlerAfterEvent $event)
    {
        $crawler = $event->getContext()->getCrawler();

        $nodes = $crawler->filterXPath('//input[(@type = \'submit\' or @type = \'button\') and not(ancestor-or-self::*[@' .Parser::ATTRIBUTE_NO_TRANSLATE. '])]/@*[name()=\'data-value\' or name()=\'data-order_button_text\']');
        foreach ($nodes as $node) {
            $text = trim($node->value);
            if ($text !== '') {
                $event->getContext()->addWord($text, function ($translated) use ($node) {
                    $node->value = $translated;
                });
            }
        }
    }
}

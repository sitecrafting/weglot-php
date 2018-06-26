<?php

namespace Weglot\Parser\Listener;

use Weglot\Client\Api\Enum\WordType;
use Weglot\Client\Api\Exception\InvalidWordTypeException;
use Weglot\Client\Api\WordEntry;
use Weglot\Parser\Event\ParserCrawlerAfterEvent;
use Weglot\Parser\Exception\ParserContextException;

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
        $inputWords = $event->getContext()->getTranslateEntry()->getInputWords();

        $nodes = $crawler->filterXPath('//text()/parent::*[not(ancestor-or-self::*[@data-wg-notranslate])]/text()');
        foreach ($nodes as $node) {
            $text = trim($node->textContent);
            $text = str_replace("\n", '', $text);
            $text = preg_replace('/\s+/', ' ', $text);

            if ($text !== '' && strpos($text, 'data-wg-notranslate') === false) {
                $index = count($inputWords);
                $path = $node->getNodePath();

                $inputWords->addOne(new WordEntry($text, WordType::TEXT));
                $event->getContext()->addToTranslateMap($index, $path);
            }
        }
    }
}

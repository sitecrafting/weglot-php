<?php

namespace Weglot\Parser\Listener;

use Weglot\Client\Api\Enum\WordType;
use Weglot\Client\Api\Exception\InvalidWordTypeException;
use Weglot\Parser\Event\ParserCrawlerAfterEvent;
use Weglot\Parser\Exception\ParserContextException;
use Weglot\Parser\Parser;

class DomMetaContentListener
{
    protected $attributes = [
        'name' => [
            'description',
            'twitter:title',
            'twitter:description',
        ],
        'property' => [
            'og:title',
            'og:description',
            'og:site_name',
        ],
    ];

    /**
     * @param ParserCrawlerAfterEvent $event
     *
     * @throws InvalidWordTypeException
     * @throws ParserContextException
     */
    public function __invoke(ParserCrawlerAfterEvent $event)
    {
        $crawler = $event->getContext()->getCrawler();

        $nodes = $crawler->filterXPath('//meta[(' .$this->makeSelectorForAttributes(). ') and not(ancestor-or-self::*[@' .Parser::ATTRIBUTE_NO_TRANSLATE. '])]/@content');
        foreach ($nodes as $node) {
            $text = trim($node->value);
            if ($text !== '') {
                $event->getContext()->addWord($text, $node->getNodePath(), function (\DOMAttr $node, $translated) {
                    $node->value = $translated;
                }, WordType::META_CONTENT);
            }
        }
    }

    protected function makeSelectorForAttributes()
    {
        $selectors = [];
        foreach ($this->attributes as $name => $values) {
            foreach ($values as $value) {
                $selectors[] = '@' .$name. ' = \'' .$value. '\'';
            }
        }
        return implode(' or ', $selectors);
    }
}

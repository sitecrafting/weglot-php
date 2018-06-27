<?php

namespace Weglot\Parser\Listener;

use Weglot\Client\Api\Enum\WordType;
use Weglot\Client\Api\Exception\InvalidWordTypeException;
use Weglot\Parser\Event\ParserCrawlerAfterEvent;
use Weglot\Util\Text;

class DomPlaceholderListener
{
    protected $attributes = [
        'type' => [
            'text',
            'password',
            'search',
            'email'
        ]
    ];

    /**
     * @param ParserCrawlerAfterEvent $event
     *
     * @throws InvalidWordTypeException
     */
    public function __invoke(ParserCrawlerAfterEvent $event)
    {
        $crawler = $event->getContext()->getCrawler();

        $nodes = $crawler->filterXPath('//*[self::input[' .$this->makeSelectorForAttributes(). '] or self::textarea]/@placeholder');
        foreach ($nodes as $node) {
            $value = Text::fullTrim($node->value);

            if ($value !== '' && !is_numeric($value) && !preg_match('/^\d+%$/', $value)) {
                $event->getContext()->addWord($value, function ($translated) use ($node) {
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

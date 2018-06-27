<?php

namespace Weglot\Parser\Listener;

use Weglot\Client\Api\Enum\WordType;
use Weglot\Client\Api\Exception\InvalidWordTypeException;
use Weglot\Parser\Event\ParserCrawlerAfterEvent;
use Weglot\Parser\Exception\ParserContextException;
use Weglot\Parser\Parser;
use Weglot\Util\Text;

class DomLinkListener
{
    /**
     * @var array
     */
    protected $attributes = [
        'title',
        'href',
        'data-value',
        'data-tooltip',
        'data-title',
        'data-text',
        'data-hover',
        'data-content'
    ];

    /**
     * @var array
     */
    protected $hrefExtensions = [
        'pdf',
        'rar',
        'docx'
    ];

    /**
     * @param ParserCrawlerAfterEvent $event
     *
     * @throws InvalidWordTypeException
     */
    public function __invoke(ParserCrawlerAfterEvent $event)
    {
        $crawler = $event->getContext()->getCrawler();

        $nodes = $crawler->filterXPath('//a[not(ancestor-or-self::*[@' .Parser::ATTRIBUTE_NO_TRANSLATE. '])]/@*[' .$this->makeSelectorForAttributes(). ']');
        foreach ($nodes as $node) {
            $value = trim($node->value);
            $type = WordType::TEXT;
            $continue = true;

            if ($node->localName === 'href') {
                $type = WordType::PDF_HREF;
                $continue = false;
                foreach ($this->hrefExtensions as $extension) {
                    $start = (\strlen($extension) + 1) * -1;
                    $continue = $continue || (strtolower(substr(Text::fullTrim($value), $start)) === ('.' .$extension));
                }
            }

            if ($value !== '' && $continue) {
                $event->getContext()->addWord($value, function ($translated) use ($node) {
                    $node->value = $translated;
                }, $type);
            }
        }
    }

    protected function makeSelectorForAttributes()
    {
        $selectors = [];
        foreach ($this->attributes as $attribute) {
            $selectors[] = 'name() = \'' .$attribute. '\'';
        }
        return implode(' or ', $selectors);
    }
}

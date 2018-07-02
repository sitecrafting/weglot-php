<?php

namespace Weglot\Parser\Listener;

use Weglot\Client\Api\Enum\WordType;
use Weglot\Parser\Parser;

final class DomMetaContentListener extends AbstractCrawlerAfterListener
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
     * {@inheritdoc}
     */
    protected function xpath()
    {
        $selectors = [];
        foreach ($this->attributes as $name => $values) {
            foreach ($values as $value) {
                $selectors[] = '@' .$name. ' = \'' .$value. '\'';
            }
        }

        return '//meta[(' .implode(' or ', $selectors). ') and not(ancestor-or-self::*[@' .Parser::ATTRIBUTE_NO_TRANSLATE. '])]/@content';
    }

    /**
     * {@inheritdoc}
     */
    protected function type(\DOMNode $node)
    {
        return WordType::META_CONTENT;
    }
}

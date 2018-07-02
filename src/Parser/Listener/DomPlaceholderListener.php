<?php

namespace Weglot\Parser\Listener;

use Weglot\Parser\Parser;

final class DomPlaceholderListener extends AbstractCrawlerAfterListener
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

        return '//*[(self::input[' .implode(' or ', $selectors). '] or self::textarea) and not(ancestor-or-self::*[@' .Parser::ATTRIBUTE_NO_TRANSLATE. '])]/@placeholder';
    }
}

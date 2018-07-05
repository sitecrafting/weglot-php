<?php

namespace Weglot\Parser\Listener;

use Weglot\Parser\Parser;

final class DomPlaceholderListener extends AbstractCrawlerAfterListener
{
    protected $types = [
        'text',
        'password',
        'search',
        'email'
    ];

    /**
     * {@inheritdoc}
     */
    protected function xpath()
    {
        $selectors = [];
        foreach ($this->types as $type) {
            $selectors[] = '@type = \'' .$type. '\'';
        }

        return '//*[(self::input[' .implode(' or ', $selectors). '] or self::textarea) and not(ancestor-or-self::*[@' .Parser::ATTRIBUTE_NO_TRANSLATE. '])]/@placeholder';
    }
}

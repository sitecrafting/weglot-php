<?php

namespace Weglot\Parser\Listener;

use Weglot\Parser\Parser;

class DomButtonListener extends AbstractCrawlerAfterListener
{
    /**
     * {@inheritdoc}
     */
    protected function xpath()
    {
        return '//*[(self::button or self::input[@type=\'submit\']) and not(ancestor-or-self::*[@' .Parser::ATTRIBUTE_NO_TRANSLATE. '])]/@value';
    }
}

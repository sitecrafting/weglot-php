<?php

namespace Weglot\Parser\Listener;

use Weglot\Parser\Parser;

final class DomSpanListener extends AbstractCrawlerAfterListener
{
    /**
     * {@inheritdoc}
     */
    protected function xpath()
    {
        return '//span[not(ancestor-or-self::*[@' .Parser::ATTRIBUTE_NO_TRANSLATE. '])]/@title';
    }
}

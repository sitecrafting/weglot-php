<?php

namespace Weglot\Parser\Listener;

use Weglot\Parser\Parser;

class DomTableDataListener extends AbstractCrawlerAfterListener
{
    /**
     * {@inheritdoc}
     */
    protected function xpath()
    {
        return '//td[not(ancestor-or-self::*[@' .Parser::ATTRIBUTE_NO_TRANSLATE. '])]/@data-title';
    }
}

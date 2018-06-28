<?php

namespace Weglot\Parser\Listener;

use Weglot\Parser\Parser;

class DomTextListener extends AbstractCrawlerAfterListener
{
    /**
     * {@inheritdoc}
     */
    protected function xpath()
    {
        return '//text()/parent::*[not(ancestor-or-self::*[@' .Parser::ATTRIBUTE_NO_TRANSLATE. '])]/text()';
    }
}

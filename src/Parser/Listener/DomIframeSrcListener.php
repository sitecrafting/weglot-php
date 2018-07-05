<?php

namespace Weglot\Parser\Listener;

use Weglot\Client\Api\Enum\WordType;
use Weglot\Parser\Parser;

final class DomIframeSrcListener extends AbstractCrawlerAfterListener
{
    /**
     * {@inheritdoc}
     */
    protected function xpath()
    {
        return '//iframe[not(ancestor-or-self::*[@' .Parser::ATTRIBUTE_NO_TRANSLATE. '])]/@src';
    }

    /**
     * {@inheritdoc}
     */
    protected function type(\DOMNode $node)
    {
        return WordType::IFRAME_SRC;
    }
}

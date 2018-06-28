<?php

namespace Weglot\Parser\Listener;

use Weglot\Client\Api\Enum\WordType;
use Weglot\Parser\Parser;

class DomInputRadioListener extends AbstractCrawlerAfterListener
{
    /**
     * {@inheritdoc}
     */
    protected function xpath()
    {
        return '//input[@type = \'radio\' and not(ancestor-or-self::*[@' .Parser::ATTRIBUTE_NO_TRANSLATE. '])]/@data-order_button_text';
    }

    /**
     * {@inheritdoc}
     */
    protected function type(\DOMNode $node)
    {
        return WordType::VALUE;
    }
}

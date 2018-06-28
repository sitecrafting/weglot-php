<?php

namespace Weglot\Parser\Listener;

use Weglot\Client\Api\Enum\WordType;
use Weglot\Parser\Parser;

class DomInputDataListener extends AbstractCrawlerAfterListener
{
    /**
     * {@inheritdoc}
     */
    protected function xpath()
    {
        return '//input[(@type = \'submit\' or @type = \'button\') and not(ancestor-or-self::*[@' .Parser::ATTRIBUTE_NO_TRANSLATE. '])]/@*[name()=\'data-value\' or name()=\'data-order_button_text\']';
    }

    /**
     * {@inheritdoc}
     */
    protected function type(\DOMNode $node)
    {
        return WordType::TEXT;
    }
}

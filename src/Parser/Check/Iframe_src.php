<?php

namespace Weglot\Parser\Check;

use Weglot\Client\Api\Enum\WordType;
use Weglot\Parser\Util\Text as TextUtil;

/**
 * Class Iframe_src
 * @package Weglot\Parser\Check
 */
class Iframe_src extends AbstractChecker
{
    /**
     * {@inheritdoc}
     */
    const DOM = 'iframe';

    /**
     * {@inheritdoc}
     */
    const PROPERTY = 'src';

    /**
     * {@inheritdoc}
     */
    const WORD_TYPE = WordType::IFRAME_SRC;

    /**
     * {@inheritdoc}
     */
    protected function check()
    {
        return TextUtil::contains(TextUtil::fullTrim($this->node->src), '.youtube.');
    }
}

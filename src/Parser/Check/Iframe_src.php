<?php

namespace Weglot\Parser\Check;

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
    protected function check()
    {
        return TextUtil::contains(TextUtil::fullTrim($this->node->src), '.youtube.');
    }
}

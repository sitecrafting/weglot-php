<?php

namespace Weglot\Parser\Check;

use Weglot\Parser\Util\Text as TextUtil;

/**
 * Class Meta_desc
 * @package Weglot\Parser\Check
 */
class Meta_desc extends AbstractChecker
{
    /**
     * {@inheritdoc}
     */
    protected function check()
    {
        return (!is_numeric(TextUtil::fullTrim($this->node->placeholder))
            && !preg_match('/^\d+%$/', TextUtil::fullTrim($this->node->placeholder)));
    }
}

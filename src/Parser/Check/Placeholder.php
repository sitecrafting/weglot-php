<?php

namespace Weglot\Parser\Check;

use Weglot\Parser\Util\Text as TextUtil;

/**
 * Class Placeholder
 * @package Weglot\Parser\Check
 */
class Placeholder extends AbstractChecker
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

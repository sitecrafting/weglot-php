<?php
/**
 * Created by PhpStorm.
 * User: bleduc
 * Date: 10/04/2018
 * Time: 11:53
 */

namespace Weglot\Parser\Check;

use \Weglot\Parser\Util\Text as TextUtil;

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

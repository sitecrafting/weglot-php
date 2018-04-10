<?php
/**
 * Created by PhpStorm.
 * User: bleduc
 * Date: 10/04/2018
 * Time: 11:55
 */

namespace Weglot\Parser\Check;

use \Weglot\Parser\Util\Text as TextUtil;

class Iframe_src extends AbstractChecker
{
    /**
     * {@inheritdoc}
     */
    protected function check()
    {
        return (strpos(TextUtil::fullTrim($this->node->src), '.youtube.') !== false);
    }
}

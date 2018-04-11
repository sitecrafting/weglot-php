<?php
/**
 * Created by PhpStorm.
 * User: bleduc
 * Date: 10/04/2018
 * Time: 11:31
 */

namespace Weglot\Parser\Check;

use \Weglot\Parser\Util\Text as TextUtil;

class Text extends AbstractChecker
{
    /**
     * {@inheritdoc}
     */
    protected function check()
    {
        return ($this->node->parent()->tag != 'script'
            && $this->node->parent()->tag != 'style'
            && !is_numeric(TextUtil::fullTrim($this->node->outertext))
            && !preg_match('/^\d+%$/', TextUtil::fullTrim($this->node->outertext))
            && strpos($this->node->outertext, '[vc_') === false);
    }
}

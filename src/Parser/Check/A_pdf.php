<?php

namespace Weglot\Parser\Check;

use Weglot\Parser\Util\Text as TextUtil;

/**
 * Class A_pdf
 * @package Weglot\Parser\Check
 */
class A_pdf extends AbstractChecker
{
    protected $extensions = [
        'pdf',
        'rar',
        'docx'
    ];

    /**
     * {@inheritdoc}
     */
    protected function check()
    {
        $boolean = false;

        foreach ($this->extensions as $extension) {
            $start = (strlen($extension) + 1) * -1;
            $boolean = $boolean || (strtolower(substr(TextUtil::fullTrim($this->node->href), $start)) === ('.' .$extension));
        }

        return $boolean;
    }
}

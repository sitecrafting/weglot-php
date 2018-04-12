<?php
/**
 * Created by PhpStorm.
 * User: bleduc
 * Date: 10/04/2018
 * Time: 11:36
 */

namespace Weglot\Parser\Check\Dom;

use Weglot\Client\Api\Enum\WordType;
use Weglot\Parser\Util\Text as TextUtil;

class Button extends AbstractDomChecker
{
    /**
     * {@inheritdoc}
     */
    const DOM = 'input[type="submit"],input[type="button"]';

    /**
     * {@inheritdoc}
     */
    const PROPERTY = 'value';

    /**
     * {@inheritdoc}
     */
    const WORD_TYPE = WordType::VALUE;

    /**
     * {@inheritdoc}
     */
    protected function check()
    {
        return (!is_numeric(TextUtil::fullTrim($this->node->value))
            && !preg_match('/^\d+%$/', TextUtil::fullTrim($this->node->value)));
    }
}

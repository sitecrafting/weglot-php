<?php

namespace Weglot\Parser\Check;

use Weglot\Client\Api\Enum\WordType;

/**
 * Class Rad_obt
 * @package Weglot\Parser\Check
 */
class Rad_obt extends AbstractChecker
{
    /**
     * {@inheritdoc}
     */
    const DOM = 'input[type="radio"]';

    /**
     * {@inheritdoc}
     */
    const PROPERTY = 'data-order_button_text';

    /**
     * {@inheritdoc}
     */
    const WORD_TYPE = WordType::VALUE;
}

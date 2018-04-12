<?php

namespace Weglot\Parser\Check;

use Weglot\Client\Api\Enum\WordType;

/**
 * Class Input_dv
 * @package Weglot\Parser\Check
 */
class Input_dv extends AbstractChecker
{
    /**
     * {@inheritdoc}
     */
    const DOM = 'input[type="submit"],input[type="button"]';

    /**
     * {@inheritdoc}
     */
    const PROPERTY = 'data-value';

    /**
     * {@inheritdoc}
     */
    const WORD_TYPE = WordType::TEXT;
}

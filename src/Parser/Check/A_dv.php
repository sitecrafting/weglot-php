<?php

namespace Weglot\Parser\Check;

use Weglot\Client\Api\Enum\WordType;

/**
 * Class A_dv
 * @package Weglot\Parser\Check
 */
class A_dv extends AbstractChecker
{
    /**
     * {@inheritdoc}
     */
    const DOM = 'a';

    /**
     * {@inheritdoc}
     */
    const PROPERTY = 'data-value';

    /**
     * {@inheritdoc}
     */
    const WORD_TYPE = WordType::TEXT;
}

<?php

namespace Weglot\Parser\Check;

use Weglot\Client\Api\Enum\WordType;

/**
 * Class A_dte
 * @package Weglot\Parser\Check
 */
class LinkDataText extends AbstractChecker
{
    /**
     * {@inheritdoc}
     */
    const DOM = 'a';

    /**
     * {@inheritdoc}
     */
    const PROPERTY = 'data-text';

    /**
     * {@inheritdoc}
     */
    const WORD_TYPE = WordType::TEXT;
}

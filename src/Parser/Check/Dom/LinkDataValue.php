<?php

namespace Weglot\Parser\Check\Dom;

use Weglot\Client\Api\Enum\WordType;

/**
 * Class A_dv
 * @package Weglot\Parser\Check
 */
class LinkDataValue extends AbstractDomChecker
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

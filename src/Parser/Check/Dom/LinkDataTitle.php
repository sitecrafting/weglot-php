<?php

namespace Weglot\Parser\Check\Dom;

use Weglot\Client\Api\Enum\WordType;

/**
 * Class A_dt
 * @package Weglot\Parser\Check
 */
class LinkDataTitle extends AbstractDomChecker
{
    /**
     * {@inheritdoc}
     */
    const DOM = 'a';

    /**
     * {@inheritdoc}
     */
    const PROPERTY = 'data-title';

    /**
     * {@inheritdoc}
     */
    const WORD_TYPE = WordType::TEXT;
}

<?php

namespace Weglot\Parser\Check;

use Weglot\Client\Api\Enum\WordType;

/**
 * Class A_dco
 * @package Weglot\Parser\Check
 */
class LinkDataContent extends AbstractChecker
{
    /**
     * {@inheritdoc}
     */
    const DOM = 'a';

    /**
     * {@inheritdoc}
     */
    const PROPERTY = 'data-content';

    /**
     * {@inheritdoc}
     */
    const WORD_TYPE = WordType::TEXT;
}

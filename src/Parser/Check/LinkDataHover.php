<?php

namespace Weglot\Parser\Check;

use Weglot\Client\Api\Enum\WordType;

/**
 * Class A_dho
 * @package Weglot\Parser\Check
 */
class LinkDataHover extends AbstractChecker
{
    /**
     * {@inheritdoc}
     */
    const DOM = 'a';

    /**
     * {@inheritdoc}
     */
    const PROPERTY = 'data-hover';

    /**
     * {@inheritdoc}
     */
    const WORD_TYPE = WordType::TEXT;
}

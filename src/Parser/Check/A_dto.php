<?php

namespace Weglot\Parser\Check;

use Weglot\Client\Api\Enum\WordType;

/**
 * Class A_dto
 * @package Weglot\Parser\Check
 */
class A_dto extends AbstractChecker
{
    /**
     * {@inheritdoc}
     */
    const DOM = 'a';

    /**
     * {@inheritdoc}
     */
    const PROPERTY = 'data-tooltip';

    /**
     * {@inheritdoc}
     */
    const WORD_TYPE = WordType::TEXT;
}

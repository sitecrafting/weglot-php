<?php

namespace Weglot\Parser\Check\Dom;

use Weglot\Client\Api\Enum\WordType;

/**
 * Class Td_dt
 * @package Weglot\Parser\Check
 */
class TdDataTitle extends AbstractDomChecker
{
    /**
     * {@inheritdoc}
     */
    const DOM = 'td';

    /**
     * {@inheritdoc}
     */
    const PROPERTY = 'data-title';

    /**
     * {@inheritdoc}
     */
    const WORD_TYPE = WordType::VALUE;
}

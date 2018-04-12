<?php

namespace Weglot\Parser\Check;

use Weglot\Client\Api\Enum\WordType;

/**
 * Class Td_dt
 * @package Weglot\Parser\Check
 */
class Td_dt extends AbstractChecker
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

<?php

namespace Weglot\Parser\Check\Dom;

use Weglot\Client\Api\Enum\WordType;

/**
 * Class A_dto
 * @package Weglot\Parser\Check
 */
class LinkDataTooltip extends AbstractDomChecker
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

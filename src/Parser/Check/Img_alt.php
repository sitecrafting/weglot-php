<?php

namespace Weglot\Parser\Check;

use Weglot\Client\Api\Enum\WordType;

/**
 * Class Img_alt
 * @package Weglot\Parser\Check
 */
class Img_alt extends AbstractChecker
{
    /**
     * {@inheritdoc}
     */
    const DOM = 'img';

    /**
     * {@inheritdoc}
     */
    const PROPERTY = 'alt';

    /**
     * {@inheritdoc}
     */
    const WORD_TYPE = WordType::IMG_ALT;
}

<?php

namespace Weglot\Parser\Check\Dom;

use Weglot\Client\Api\Enum\WordType;

/**
 * Class Img_alt
 * @package Weglot\Parser\Check
 */
class ImageAlt extends AbstractDomChecker
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

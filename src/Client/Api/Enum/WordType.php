<?php
/**
 * Created by PhpStorm.
 * User: bleduc
 * Date: 09/04/2018
 * Time: 12:01
 */

namespace Weglot\Client\Api\Enum;

/**
 * Enum WordType
 * Used to define where was the text we are parsing
 *
 * @package Weglot\Client\Api\Enum
 */
abstract class WordType
{
    const GENERIC = 0;
    const TEXT = 1;
    const VALUE = 2;
    const PLACEHOLDER = 3;
    const META_CONTENT = 4;
    const IFRAME_SRC = 5;
    const IMG_SRC = 6;
    const IMG_ALT = 7;
    const PDF_HREF = 8;
}

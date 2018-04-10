<?php
/**
 * Created by PhpStorm.
 * User: bleduc
 * Date: 10/04/2018
 * Time: 10:45
 */

namespace Weglot\Parser\Util;

class Text
{
    /**
     * @param string $word
     * @return string
     */
    public static function fullTrim($word)
    {
        return trim($word, " \t\n\r\0\x0B\xA0�");
    }
}

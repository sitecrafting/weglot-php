<?php
/**
 * @author Remy Berda
 * User: remy
 * Date: 17/06/2019
 * Time: 15:15
 */

namespace Weglot\Parser\Check\Regex;


use Weglot\Util\SourceType;

class JsonLdChecker extends RegexChecker
{
    const REGEX = '#<script type=\'application\/ld\+json\'>((.|\n)*?)<\/script>#';

    const TYPE = SourceType::SOURCE_JSON;

    public static $KEYS = array( "description" ,  "name" , "headline" , "articleSection" ); // Can't put a const because PHP 5.4 + 5.5 doesn't allow array in class constants.
}
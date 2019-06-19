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

    const KEYS = array( "description" ,  "name" , "headline" , "articleSection" );
}
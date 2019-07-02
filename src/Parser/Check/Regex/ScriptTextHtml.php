<?php
/**
 * @author Remy Berda
 * User: remy
 * Date: 17/06/2019
 * Time: 15:15
 */

namespace Weglot\Parser\Check\Regex;


use Weglot\Util\SourceType;

class ScriptTextHtml extends RegexChecker
{
    const REGEX = '#<script type="text/html"([^\>]+?)?>((.|\n)*?)<\/script>#';

    const TYPE = SourceType::SOURCE_HTML;

    const VAR_NUMBER = 2;

    public static $KEYS = array( );
}
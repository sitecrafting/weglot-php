<?php
/**
 * @author Remy Berda
 * User: remy
 * Date: 17/06/2019
 * Time: 15:15
 */

namespace Weglot\Parser\Check\Regex;


use Weglot\Util\SourceType;

class ContactFormChecker extends RegexChecker
{
    const REGEX = '#form.settings=(.*?);#';

    const TYPE = SourceType::SOURCE_JSON;

    const KEYS = array( "title" , "label" , "validateRequiredField" , "formErrorsCorrectErrors");
}
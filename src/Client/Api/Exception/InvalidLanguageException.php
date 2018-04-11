<?php

namespace Weglot\Client\Api\Exception;

use Throwable;

/**
 * Class InvalidLanguageException
 * @package Weglot\Client\Api\Exception
 */
class InvalidLanguageException extends \Exception
{
    public function __construct($message = 'The given language is invalid', $code = 400, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

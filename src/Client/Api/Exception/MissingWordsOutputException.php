<?php

namespace Weglot\Client\Api\Exception;

use Throwable;

/**
 * Class MissingWordsOutputException
 * @package Weglot\Client\Api\Exception
 */
class MissingWordsOutputException extends \Exception
{
    public function __construct($message = 'There is no output words', $code = 400, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

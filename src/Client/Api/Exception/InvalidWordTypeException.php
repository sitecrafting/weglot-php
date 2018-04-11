<?php

namespace Weglot\Client\Api\Exception;

use Throwable;

/**
 * Class InvalidWordTypeException
 * @package Weglot\Client\Api\Exception
 */
class InvalidWordTypeException extends \Exception
{
    public function __construct($message = 'The given WordType is invalid', $code = 400, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

<?php

namespace Weglot\Client\Api\Exception;

use Throwable;

/**
 * Class InputAndOutputCountMatchException
 * @package Weglot\Client\Api\Exception
 */
class InputAndOutputCountMatchException extends \Exception
{
    public function __construct($message = "Input and ouput words count doesn't match.", $code = 400, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

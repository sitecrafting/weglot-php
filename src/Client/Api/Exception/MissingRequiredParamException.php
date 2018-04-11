<?php

namespace Weglot\Client\Api\Exception;

use Throwable;

/**
 * Class MissingRequiredParamException
 * @package Weglot\Client\Api\Exception
 */
class MissingRequiredParamException extends \Exception
{
    public function __construct($message = 'Required fields for $params are: language_from, language_to, bot, request_url', $code = 400, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

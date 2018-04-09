<?php
/**
 * Created by PhpStorm.
 * User: bleduc
 * Date: 09/04/2018
 * Time: 11:16
 */

namespace Weglot\Client\Api\Exception;

use Throwable;

class MissingRequiredParamException extends \Exception
{
    public function __construct($message = 'Required fields for $params are: language_from, language_to, bot, request_url', $code = 400, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

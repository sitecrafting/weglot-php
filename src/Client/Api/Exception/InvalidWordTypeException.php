<?php
/**
 * Created by PhpStorm.
 * User: bleduc
 * Date: 09/04/2018
 * Time: 11:16
 */

namespace Weglot\Client\Api\Exception;

use Throwable;

class InvalidWordTypeException extends \Exception
{
    public function __construct($message = 'The given WordType is invalid', $code = 400, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

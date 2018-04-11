<?php
/**
 * Created by PhpStorm.
 * User: bleduc
 * Date: 09/04/2018
 * Time: 18:12
 */

namespace Weglot\Client\Api\Exception;

use Throwable;

class InputAndOutputCountMatchException extends \Exception
{
    public function __construct($message = "Input and ouput words count doesn't match.", $code = 400, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

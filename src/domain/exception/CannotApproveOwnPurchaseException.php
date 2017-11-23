<?php

namespace domain\exception;

use Exception;
use Throwable;

class CannotApproveOwnPurchaseException extends Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

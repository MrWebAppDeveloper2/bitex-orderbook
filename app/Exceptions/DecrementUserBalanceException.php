<?php

namespace App\Exceptions;

use Exception;

class DecrementUserBalanceException extends Exception
{
    /**
     * Create a new class instance.
     */
    public function __construct(protected $message)
    {
    }
}

<?php

namespace App\Exceptions;

use Exception;

class TradeException extends Exception
{
    /**
     * Create a new class instance.
     */
    public function __construct(protected $message)
    {
    }
}

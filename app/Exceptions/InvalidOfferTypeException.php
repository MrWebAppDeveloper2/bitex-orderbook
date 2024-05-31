<?php

namespace App\Exceptions;

use Exception;

class InvalidOfferTypeException extends Exception
{
    /**
     * Create a new class instance.
     */
    public function __construct(protected $message)
    {
    }
}

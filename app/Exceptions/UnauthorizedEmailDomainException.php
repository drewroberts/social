<?php

namespace App\Exceptions;

use Exception;

class UnauthorizedEmailDomainException extends Exception
{
    /**
     * Create a new exception instance.
     */
    public function __construct()
    {
        parent::__construct('You are outside the organization');
    }
}

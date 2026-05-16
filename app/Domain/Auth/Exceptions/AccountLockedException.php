<?php


namespace App\Domain\Auth\Exceptions;

use Exception;

class AccountLockedException extends Exception
{
    public function __construct(string $message = 'Account is temporarily locked due to too many failed login attempts.')
    {
        parent::__construct($message);
    }
}

<?php


namespace App\Domain\Auth\Exceptions;

use Exception;

class PrivilegeEscalationException extends Exception
{
    public function __construct(string $message = 'You cannot assign a role with higher privileges than your own.')
    {
        parent::__construct($message);
    }
}

<?php


namespace App\Domain\Auth\Actions;

use App\Models\User;

class VerifyEmailAction
{
    public function execute(User $user): bool
    {
        if ($user->hasVerifiedEmail()) {
            return false;
        }

        $user->markEmailAsVerified();

        return true;
    }
}

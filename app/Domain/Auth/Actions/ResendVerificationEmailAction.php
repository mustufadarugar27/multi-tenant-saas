<?php


namespace App\Domain\Auth\Actions;

use App\Models\User;
use Illuminate\Auth\Events\Registered;

class ResendVerificationEmailAction
{
    public function execute(User $user): bool
    {
        if ($user->hasVerifiedEmail()) {
            return false;
        }

        $user->sendEmailVerificationNotification();

        return true;
    }
}

<?php


namespace App\Domain\Auth\Actions;

use App\Models\User;

class LogoutAllDevicesAction
{
    public function execute(User $user): void
    {
        $user->tokens()->delete();
    }
}

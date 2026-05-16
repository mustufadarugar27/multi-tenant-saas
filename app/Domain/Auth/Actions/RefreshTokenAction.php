<?php


namespace App\Domain\Auth\Actions;

use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;

class RefreshTokenAction
{
    public function execute(User $user, PersonalAccessToken $currentToken): array
    {
        $deviceName = $currentToken->name;
        $currentToken->delete();

        $token = $user->createToken($deviceName)->plainTextToken;

        return ['token' => $token, 'token_type' => 'Bearer'];
    }
}

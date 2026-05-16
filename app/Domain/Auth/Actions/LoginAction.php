<?php


namespace App\Domain\Auth\Actions;

use App\Domain\Auth\DTOs\LoginDTO;
use App\Domain\Auth\Exceptions\AccountLockedException;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class LoginAction
{
    /**
     * @throws AccountLockedException
     */
    public function execute(LoginDTO $dto, Tenant $tenant): ?array
    {
        $user = User::where('email', $dto->email)->first();

        if ($user === null) {
            return null;
        }

        if ($user->isLocked()) {
            throw new AccountLockedException();
        }

        if (! Hash::check($dto->password, $user->password)) {
            $user->incrementFailedLogins();
            return null;
        }

        $user->clearFailedLogins();
        $user->update(['last_login_at' => now()]);

        $token = $user->createToken($dto->deviceName)->plainTextToken;

        return compact('user', 'token');
    }
}

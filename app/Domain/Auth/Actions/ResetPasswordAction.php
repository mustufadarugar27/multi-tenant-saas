<?php


namespace App\Domain\Auth\Actions;

use App\Domain\Auth\DTOs\ResetPasswordDTO;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ResetPasswordAction
{
    public function execute(ResetPasswordDTO $dto): bool
    {
        $record = DB::table('password_reset_tokens')
            ->where('email', $dto->email)
            ->first();

        if ($record === null || ! Hash::check($dto->token, $record->token)) {
            return false;
        }

        if (now()->diffInMinutes($record->created_at) > 60) {
            DB::table('password_reset_tokens')->where('email', $dto->email)->delete();
            return false;
        }

        $user = User::where('email', $dto->email)->first();

        if ($user === null) {
            return false;
        }

        $user->update([
            'password'            => Hash::make($dto->password),
            'password_changed_at' => now(),
        ]);

        $user->tokens()->delete();

        DB::table('password_reset_tokens')->where('email', $dto->email)->delete();

        return true;
    }
}

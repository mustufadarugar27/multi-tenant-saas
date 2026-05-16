<?php


namespace App\Domain\Auth\Actions;

use App\Domain\Auth\DTOs\ForgotPasswordDTO;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ForgotPasswordAction
{
    public function execute(ForgotPasswordDTO $dto): ?string
    {
        $user = User::where('email', $dto->email)->first();

        if ($user === null) {
            return null;
        }

        // Clean old tokens for this email
        DB::table('password_reset_tokens')->where('email', $dto->email)->delete();

        $token = Str::random(64);

        DB::table('password_reset_tokens')->insert([
            'email'      => $dto->email,
            'token'      => Hash::make($token),
            'created_at' => now(),
        ]);

        return $token;
    }
}

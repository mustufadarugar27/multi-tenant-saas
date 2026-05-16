<?php


namespace App\Http\Controllers\Api\V1\Auth;

use App\Domain\Auth\Actions\ForgotPasswordAction;
use App\Domain\Auth\Actions\ResetPasswordAction;
use App\Domain\Auth\DTOs\ForgotPasswordDTO;
use App\Domain\Auth\DTOs\ResetPasswordDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Notifications\Auth\PasswordResetNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class PasswordResetController extends Controller
{
    public function __construct(
        private readonly ForgotPasswordAction $forgotPassword,
        private readonly ResetPasswordAction $resetPassword,
    ) {}

    /**
     * POST /api/v1/auth/forgot-password
     *
     * Always returns 200 regardless of whether the email exists (user enumeration prevention).
     * Tenancy is already initialized by IdentifyTenant middleware before this controller runs.
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $dto = ForgotPasswordDTO::fromArray($request->validated());
        $token = $this->forgotPassword->execute($dto);

        if ($token !== null) {
            $user = app(\App\Domain\Auth\Repositories\Contracts\UserRepositoryInterface::class)
                ->findByEmail($dto->email);

            $user?->notify(new PasswordResetNotification($token));
        }

        return response()->json([
            'message' => 'If an account with that email exists, a password reset link has been sent.',
        ]);
    }

    /**
     * POST /api/v1/auth/reset-password
     *
     * Tenancy is already initialized by IdentifyTenant middleware.
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $dto = ResetPasswordDTO::fromArray($request->validated());
        $success = $this->resetPassword->execute($dto);

        if (! $success) {
            return response()->json([
                'message' => 'This password reset token is invalid or has expired.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return response()->json(['message' => 'Password has been reset successfully.']);
    }
}

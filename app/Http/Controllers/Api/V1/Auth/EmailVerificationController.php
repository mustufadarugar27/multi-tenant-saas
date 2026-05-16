<?php


namespace App\Http\Controllers\Api\V1\Auth;

use App\Domain\Auth\Actions\ResendVerificationEmailAction;
use App\Domain\Auth\Actions\VerifyEmailAction;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EmailVerificationController extends Controller
{
    public function __construct(
        private readonly VerifyEmailAction $verifyEmail,
        private readonly ResendVerificationEmailAction $resendVerification,
    ) {}

    /**
     * GET /api/v1/auth/email/verify/{id}/{hash}
     *
     * Laravel's signed URL verification.
     */
    public function verify(EmailVerificationRequest $request): JsonResponse
    {
        $verified = $this->verifyEmail->execute($request->user());

        if ($verified) {
            event(new Verified($request->user()));
        }

        return response()->json([
            'message' => $verified ? 'Email verified successfully.' : 'Email already verified.',
            'verified' => true,
        ]);
    }

    /**
     * POST /api/v1/auth/email/resend
     */
    public function resend(Request $request): JsonResponse
    {
        $sent = $this->resendVerification->execute($request->user());

        if (! $sent) {
            return response()->json([
                'message' => 'Email is already verified.',
            ], Response::HTTP_CONFLICT);
        }

        return response()->json(['message' => 'Verification email sent.']);
    }
}

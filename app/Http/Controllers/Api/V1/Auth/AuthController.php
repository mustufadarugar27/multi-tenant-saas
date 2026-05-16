<?php


namespace App\Http\Controllers\Api\V1\Auth;

use App\Domain\Auth\Actions\LoginAction;
use App\Domain\Auth\Actions\LogoutAllDevicesAction;
use App\Domain\Auth\Actions\RefreshTokenAction;
use App\Domain\Auth\Actions\RegisterCompanyAction;
use App\Domain\Auth\Exceptions\AccountLockedException;
use App\Domain\Auth\Services\ActivityLogService;
use App\Domain\Tenant\Services\TenantResolverService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterCompanyRequest;
use App\Http\Resources\Auth\AuthResource;
use App\Http\Resources\Tenant\TenantResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    public function __construct(
        private readonly RegisterCompanyAction $registerCompany,
        private readonly LoginAction $login,
        private readonly LogoutAllDevicesAction $logoutAll,
        private readonly RefreshTokenAction $refreshToken,
        private readonly TenantResolverService $tenantResolver,
        private readonly ActivityLogService $activityLog,
    ) {}

    public function register(RegisterCompanyRequest $request): JsonResponse
    {
        $result = $this->registerCompany->execute($request->toDTO());

        return (new AuthResource($result['user']))
            ->withToken($result['token'])
            ->additional([
                'tenant' => new TenantResource($result['tenant']),
            ])
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $tenant = $this->tenantResolver->resolveFromRequest($request);

        if ($tenant === null) {
            abort(404, 'Tenant not found.');
        }

        try {
            $result = $this->login->execute($request->toDTO(), $tenant);
        } catch (AccountLockedException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], Response::HTTP_TOO_MANY_REQUESTS);
        }

        if ($result === null) {
            return response()->json([
                'message' => 'These credentials do not match our records.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return (new AuthResource($result['user']))
            ->withToken($result['token'])
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        $user->currentAccessToken()->delete();

        $this->activityLog->log(
            event: 'auth.logout',
            description: 'User logged out',
            userId: $user->id,
            request: $request,
        );

        return response()->json(['message' => 'Logged out successfully.']);
    }

    public function logoutAll(Request $request): JsonResponse
    {
        $this->logoutAll->execute($request->user());

        return response()->json(['message' => 'All sessions revoked.']);
    }

    public function refresh(Request $request): JsonResponse
    {
        $currentToken = $request->user()->currentAccessToken();

        $result = $this->refreshToken->execute($request->user(), $currentToken);

        return response()->json($result);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load(['tenant', 'roles', 'permissions']);

        return response()->json([
            'user' => new AuthResource($user),
            'tenant' => new TenantResource($user->tenant),
        ]);
    }
}

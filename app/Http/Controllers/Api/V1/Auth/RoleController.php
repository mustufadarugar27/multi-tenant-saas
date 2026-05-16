<?php


namespace App\Http\Controllers\Api\V1\Auth;

use App\Domain\Auth\Actions\AssignRoleAction;
use App\Domain\Auth\Actions\RevokeRoleAction;
use App\Domain\Auth\DTOs\AssignRoleDTO;
use App\Domain\Auth\Exceptions\PrivilegeEscalationException;
use App\Domain\Auth\Repositories\Contracts\UserRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\AssignRoleRequest;
use App\Http\Requests\Auth\RevokeRoleRequest;
use App\Http\Resources\Auth\RoleResource;
use App\Http\Resources\Auth\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function __construct(
        private readonly AssignRoleAction $assignRole,
        private readonly RevokeRoleAction $revokeRole,
        private readonly UserRepositoryInterface $users,
    ) {}

    public function index(): JsonResponse
    {
        $this->authorize('manage-rbac');

        $roles = Role::with('permissions')->get();

        return response()->json([
            'data' => RoleResource::collection($roles),
        ]);
    }

    public function assign(AssignRoleRequest $request, string $userId): JsonResponse
    {
        $target = $this->users->findById($userId);

        if ($target === null) {
            abort(404, 'User not found.');
        }

        $this->authorize('assignRole', $target);

        try {
            $this->assignRole->execute(
                AssignRoleDTO::fromArray($request->validated()),
                $request->user(),
                $target,
            );
        } catch (PrivilegeEscalationException $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_FORBIDDEN);
        }

        return response()->json(['message' => 'Role assigned successfully.']);
    }

    public function revoke(RevokeRoleRequest $request, string $userId, string $role): JsonResponse
    {
        $target = $this->users->findById($userId);

        if ($target === null) {
            abort(404, 'User not found.');
        }

        $this->authorize('assignRole', $target);

        try {
            $this->revokeRole->execute($role, $request->user(), $target);
        } catch (PrivilegeEscalationException $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_FORBIDDEN);
        }

        return response()->json(['message' => 'Role revoked successfully.']);
    }
}

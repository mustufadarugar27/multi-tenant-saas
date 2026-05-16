<?php


namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\Auth\PermissionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionController extends Controller
{
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Permission::class);

        $permissions = Permission::all()->groupBy(fn ($p) => explode('.', $p->name)[0]);

        return response()->json([
            'data' => $permissions->map(fn ($group) => PermissionResource::collection($group)),
        ]);
    }

    public function forRole(string $roleName): JsonResponse
    {
        $this->authorize('viewAny', Permission::class);

        $role = Role::where('name', $roleName)->firstOrFail();

        return response()->json([
            'role' => $role->name,
            'permissions' => PermissionResource::collection($role->permissions),
        ]);
    }

    public function grantToRole(Request $request, string $roleName, string $permissionName): JsonResponse
    {
        $this->authorize('manage', Permission::class);

        $role = Role::where('name', $roleName)->firstOrFail();
        $permission = Permission::where('name', $permissionName)->firstOrFail();

        $role->givePermissionTo($permission);

        return response()->json(['message' => "Permission '{$permissionName}' granted to role '{$roleName}'."]);
    }

    public function revokeFromRole(Request $request, string $roleName, string $permissionName): JsonResponse
    {
        $this->authorize('manage', Permission::class);

        $role = Role::where('name', $roleName)->firstOrFail();
        $permission = Permission::where('name', $permissionName)->firstOrFail();

        $role->revokePermissionTo($permission);

        return response()->json(['message' => "Permission '{$permissionName}' revoked from role '{$roleName}'."]);
    }
}

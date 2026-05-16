<?php


namespace App\Http\Resources\Auth;

use App\Support\EnumConfig;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\User
 */
class AuthResource extends JsonResource
{
    private ?string $token = null;

    public function withToken(string $token): static
    {
        $this->token = $token;

        return $this;
    }

    public function toArray(Request $request): array
    {
        $data = [
            'user' => [
                'id' => $this->id,
                'name' => $this->name,
                'email' => $this->email,
                'role' => $this->role,
                'role_label' => EnumConfig::label('user_role', $this->role),
                'tenant_id' => $this->tenant_id,
                'email_verified_at' => $this->email_verified_at?->toIso8601String(),
                'last_login_at' => $this->last_login_at?->toIso8601String(),
                'is_active' => $this->is_active,
                'permissions' => $this->whenLoaded('permissions', fn () => $this->getAllPermissions()->pluck('name')),
                'roles' => $this->whenLoaded('roles', fn () => $this->getRoleNames()),
                'created_at' => $this->created_at->toIso8601String(),
            ],
        ];

        if ($this->token !== null) {
            $data['token'] = $this->token;
            $data['token_type'] = 'Bearer';
        }

        return $data;
    }
}

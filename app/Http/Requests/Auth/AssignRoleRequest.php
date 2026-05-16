<?php


namespace App\Http\Requests\Auth;

use App\Support\EnumConfig;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;   // Authorization handled in RoleController via policy
    }

    public function rules(): array
    {
        return [
            'role' => ['required', 'string', Rule::in(EnumConfig::values('user_role'))],
        ];
    }
}

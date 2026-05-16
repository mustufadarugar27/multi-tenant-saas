<?php


namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RevokeRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;   // Authorization handled in RoleController via policy
    }

    public function rules(): array
    {
        return [];
    }
}

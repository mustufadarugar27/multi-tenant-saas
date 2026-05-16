<?php


namespace App\Http\Requests\Auth;

use App\Domain\Auth\DTOs\RegisterCompanyDTO;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_name' => ['required', 'string', 'min:2', 'max:100'],
            'slug' => ['sometimes', 'string', 'min:2', 'max:50', 'regex:/^[a-z0-9\-]+$/'],
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['required', 'email:rfc', 'max:254'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ];
    }

    public function toDTO(): RegisterCompanyDTO
    {
        return RegisterCompanyDTO::fromArray($this->validated());
    }
}

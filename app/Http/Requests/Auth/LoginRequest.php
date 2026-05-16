<?php


namespace App\Http\Requests\Auth;

use App\Domain\Auth\DTOs\LoginDTO;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['sometimes', 'string', 'max:100'],
        ];
    }

    public function toDTO(): LoginDTO
    {
        return LoginDTO::fromArray($this->validated());
    }
}

<?php


namespace App\Http\Requests\Registration;

use App\Domain\Billing\DTOs\InitiateRegistrationDTO;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class InitiateRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_name'  => ['required', 'string', 'max:255'],
            'slug'          => ['nullable', 'string', 'max:63', 'alpha_dash', Rule::unique('tenants', 'slug'), Rule::unique('pending_registrations', 'slug')->where(fn ($q) => $q->where('status', 'pending')->where('expires_at', '>', now()))],
            'name'          => ['required', 'string', 'max:255'],
            'email'         => ['required', 'email', 'max:255'],
            'password'      => ['required', 'string', 'min:8', 'confirmed'],
            'plan_slug'     => ['required', 'string', Rule::exists('plans', 'slug')->where('is_active', true)],
            'billing_cycle' => ['required', 'string', Rule::in(['monthly', 'yearly'])],
        ];
    }

    public function toDTO(): InitiateRegistrationDTO
    {
        return InitiateRegistrationDTO::fromRequest($this);
    }
}

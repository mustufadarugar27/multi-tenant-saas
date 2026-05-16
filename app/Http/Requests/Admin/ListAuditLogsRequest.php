<?php


namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ListAuditLogsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // authorization handled by controller policy
    }

    public function rules(): array
    {
        return [
            'actor_id' => ['sometimes', 'uuid'],
            'action' => ['sometimes', 'string', 'max:100'],
            'resource_type' => ['sometimes', 'string', 'max:150'],
            'resource_id' => ['sometimes', 'uuid'],
            'status' => ['sometimes', 'in:success,failure'],
            'from' => ['sometimes', 'date'],
            'to' => ['sometimes', 'date', 'after_or_equal:from'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function filters(): array
    {
        return $this->only(['actor_id', 'action', 'resource_type', 'resource_id', 'status', 'from', 'to']);
    }
}

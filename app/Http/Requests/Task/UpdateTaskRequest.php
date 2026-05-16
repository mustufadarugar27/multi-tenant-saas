<?php


namespace App\Http\Requests\Task;

use App\DataTransferObjects\Task\UpdateTaskDTO;
use App\Support\EnumConfig;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string', 'max:10000'],
            'assigned_to' => ['sometimes', 'nullable', 'uuid', 'exists:users,id'],
            'priority' => ['sometimes', 'string', Rule::in(EnumConfig::values('task_priority'))],
            'status' => ['sometimes', 'string', Rule::in(EnumConfig::values('task_status'))],
            'due_date' => ['sometimes', 'nullable', 'date_format:Y-m-d'],
            'estimated_hours' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:9999.99'],
            'actual_hours' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:9999.99'],
        ];
    }

    public function toDTO(): UpdateTaskDTO
    {
        return UpdateTaskDTO::fromArray($this->validated());
    }
}

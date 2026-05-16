<?php


namespace App\Http\Requests\Task;

use App\Support\EnumConfig;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListTasksRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_id' => ['sometimes', 'uuid'],
            'assigned_to' => ['sometimes', 'uuid'],
            'status' => ['sometimes', 'string', Rule::in(EnumConfig::values('task_status'))],
            'priority' => ['sometimes', 'string', Rule::in(EnumConfig::values('task_priority'))],
            'search' => ['sometimes', 'string', 'max:255'],
            'due_from' => ['sometimes', 'date_format:Y-m-d'],
            'due_to' => ['sometimes', 'date_format:Y-m-d', 'after_or_equal:due_from'],
            'overdue' => ['sometimes', 'boolean'],
            'sort_by' => ['sometimes', 'string', Rule::in(['title', 'status', 'priority', 'due_date', 'created_at'])],
            'sort_dir' => ['sometimes', 'string', Rule::in(['asc', 'desc'])],
            'per_page' => ['sometimes', 'integer', 'min:5', 'max:100'],
        ];
    }

    public function filters(): array
    {
        return $this->only([
            'project_id', 'assigned_to', 'status', 'priority',
            'search', 'due_from', 'due_to', 'overdue',
            'sort_by', 'sort_dir',
        ]);
    }

    public function perPage(): int
    {
        return (int) ($this->validated()['per_page'] ?? 20);
    }
}

<?php


namespace App\Http\Requests\Project;

use App\DataTransferObjects\Project\UpdateProjectDTO;
use App\Support\EnumConfig;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'start_date' => ['sometimes', 'nullable', 'date_format:Y-m-d'],
            'end_date' => ['sometimes', 'nullable', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'budget' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:999999999.99'],
            'status' => ['sometimes', 'required', 'string', Rule::in(EnumConfig::values('project_status'))],
        ];
    }

    public function toDTO(): UpdateProjectDTO
    {
        return UpdateProjectDTO::fromArray($this->validated());
    }
}

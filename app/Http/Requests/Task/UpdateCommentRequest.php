<?php


namespace App\Http\Requests\Task;

use App\DataTransferObjects\Task\UpdateCommentDTO;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'content' => ['required', 'string', 'min:1', 'max:10000'],
        ];
    }

    public function toDTO(): UpdateCommentDTO
    {
        return UpdateCommentDTO::fromArray($this->validated());
    }
}

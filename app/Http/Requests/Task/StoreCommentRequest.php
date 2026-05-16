<?php


namespace App\Http\Requests\Task;

use App\DataTransferObjects\Task\CreateCommentDTO;
use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'content' => ['required', 'string', 'min:1', 'max:10000'],
            'parent_id' => ['sometimes', 'nullable', 'uuid', 'exists:task_comments,id'],
        ];
    }

    public function toDTO(string $taskId): CreateCommentDTO
    {
        $validated = $this->validated();

        return new CreateCommentDTO(
            taskId: $taskId,
            userId: $this->user()->id,
            content: $validated['content'],
            parentId: $validated['parent_id'] ?? null,
        );
    }
}

<?php


namespace App\DataTransferObjects\Task;

final readonly class CreateCommentDTO
{
    public function __construct(
        public string $taskId,
        public string $userId,
        public string $content,
        public ?string $parentId = null,
    ) {}
}

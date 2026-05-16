<?php


namespace App\DataTransferObjects\Task;

final readonly class UpdateCommentDTO
{
    public function __construct(
        public string $content,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(content: $data['content']);
    }
}

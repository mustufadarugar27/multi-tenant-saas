<?php


namespace App\DataTransferObjects\Task;

use Illuminate\Http\UploadedFile;

class UploadAttachmentDTO
{
    public function __construct(
        public readonly string $taskId,
        public readonly string $userId,
        public readonly UploadedFile $file,
    ) {}
}

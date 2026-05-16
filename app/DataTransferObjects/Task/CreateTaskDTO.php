<?php


namespace App\DataTransferObjects\Task;

final readonly class CreateTaskDTO
{
    public function __construct(
        public string $projectId,
        public string $title,
        public ?string $description,
        public ?string $assignedTo,
        public string $priority,
        public string $status,
        public ?string $dueDate,
        public ?float $estimatedHours,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            projectId: $data['project_id'],
            title: $data['title'],
            description: $data['description'] ?? null,
            assignedTo: $data['assigned_to'] ?? null,
            priority: $data['priority'] ?? 'medium',
            status: $data['status'] ?? 'todo',
            dueDate: $data['due_date'] ?? null,
            estimatedHours: isset($data['estimated_hours']) ? (float) $data['estimated_hours'] : null,
        );
    }
}

<?php


namespace App\DataTransferObjects\Task;

final readonly class UpdateTaskDTO
{
    /**
     * @param string[] $provided Keys explicitly sent in the request (enables partial updates).
     */
    public function __construct(
        public array $provided,
        public ?string $title = null,
        public ?string $description = null,
        public ?string $assignedTo = null,
        public ?string $priority = null,
        public ?string $status = null,
        public ?string $dueDate = null,
        public ?float $estimatedHours = null,
        public ?float $actualHours = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            provided: array_keys($data),
            title: $data['title'] ?? null,
            description: $data['description'] ?? null,
            assignedTo: $data['assigned_to'] ?? null,
            priority: $data['priority'] ?? null,
            status: $data['status'] ?? null,
            dueDate: $data['due_date'] ?? null,
            estimatedHours: isset($data['estimated_hours']) ? (float) $data['estimated_hours'] : null,
            actualHours: isset($data['actual_hours']) ? (float) $data['actual_hours'] : null,
        );
    }

    public function has(string $field): bool
    {
        return in_array($field, $this->provided, true);
    }
}

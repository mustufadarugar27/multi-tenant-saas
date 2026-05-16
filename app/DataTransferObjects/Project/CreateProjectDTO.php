<?php


namespace App\DataTransferObjects\Project;

final readonly class CreateProjectDTO
{
    public function __construct(
        public string $name,
        public ?string $description,
        public ?string $startDate,
        public ?string $endDate,
        public ?float $budget,
        public string $status,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            description: $data['description'] ?? null,
            startDate: $data['start_date'] ?? null,
            endDate: $data['end_date'] ?? null,
            budget: isset($data['budget']) ? (float) $data['budget'] : null,
            status: $data['status'] ?? 'draft',
        );
    }
}

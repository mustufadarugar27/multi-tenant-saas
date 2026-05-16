<?php


namespace App\DataTransferObjects\Project;

final readonly class UpdateProjectDTO
{
    /**
     * @param string[] $provided Keys explicitly sent in the request (enables partial updates).
     */
    public function __construct(
        public array $provided,
        public ?string $name = null,
        public ?string $description = null,
        public ?string $startDate = null,
        public ?string $endDate = null,
        public ?float $budget = null,
        public ?string $status = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            provided: array_keys($data),
            name: $data['name'] ?? null,
            description: $data['description'] ?? null,
            startDate: $data['start_date'] ?? null,
            endDate: $data['end_date'] ?? null,
            budget: isset($data['budget']) ? (float) $data['budget'] : null,
            status: $data['status'] ?? null,
        );
    }

    public function has(string $field): bool
    {
        return in_array($field, $this->provided, true);
    }
}

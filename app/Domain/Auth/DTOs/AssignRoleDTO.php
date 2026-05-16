<?php


namespace App\Domain\Auth\DTOs;

readonly class AssignRoleDTO
{
    public function __construct(public string $role) {}

    public static function fromArray(array $data): self
    {
        return new self(role: $data['role']);
    }
}

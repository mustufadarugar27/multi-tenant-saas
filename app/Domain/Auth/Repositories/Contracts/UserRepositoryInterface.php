<?php


namespace App\Domain\Auth\Repositories\Contracts;

use App\Models\User;

interface UserRepositoryInterface
{
    public function findById(string $id): ?User;

    public function findByEmail(string $email): ?User;

    public function create(array $data): User;
}

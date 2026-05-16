<?php


namespace App\Domain\Auth\DTOs;

readonly class LoginDTO
{
    public function __construct(
        public string $email,
        public string $password,
        public string $deviceName,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            email:      $data['email'],
            password:   $data['password'],
            deviceName: $data['device_name'] ?? 'web',
        );
    }
}

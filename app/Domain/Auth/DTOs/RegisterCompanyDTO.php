<?php


namespace App\Domain\Auth\DTOs;

readonly class RegisterCompanyDTO
{
    public function __construct(
        public string $companyName,
        public string $slug,
        public string $name,
        public string $email,
        public string $password,
    ) {}

    public static function fromArray(array $data): self
    {
        $companyName = $data['company_name'];
        $slug = $data['slug'] ?? \Illuminate\Support\Str::slug($companyName);

        return new self(
            companyName: $companyName,
            slug:        $slug,
            name:        $data['name'],
            email:       $data['email'],
            password:    $data['password'],
        );
    }
}

<?php


namespace App\Support;

final class EnumOption
{
    public function __construct(
        public readonly string $value,
        private readonly string $labelText,
    ) {}

    public function label(): string
    {
        return $this->labelText;
    }
}

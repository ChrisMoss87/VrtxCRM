<?php

declare(strict_types=1);

namespace App\Domain\Modules\Entities;

use DateTimeImmutable;

final class FieldOption
{
    public function __construct(
        private ?int $id,
        private int $fieldId,
        private string $label,
        private string $value,
        private ?string $color,
        private bool $isDefault,
        private bool $isActive,
        private int $order,
        private DateTimeImmutable $createdAt,
        private ?DateTimeImmutable $updatedAt = null,
    ) {}

    public static function create(
        int $fieldId,
        string $label,
        string $value,
        ?string $color = null,
        bool $isDefault = false,
        int $order = 0
    ): self {
        return new self(
            id: null,
            fieldId: $fieldId,
            label: $label,
            value: $value,
            color: $color,
            isDefault: $isDefault,
            isActive: true,
            order: $order,
            createdAt: new DateTimeImmutable,
        );
    }

    public function activate(): void
    {
        $this->isActive = true;
    }

    public function deactivate(): void
    {
        $this->isActive = false;
    }

    public function setAsDefault(): void
    {
        $this->isDefault = true;
    }

    public function unsetAsDefault(): void
    {
        $this->isDefault = false;
    }

    public function updateDetails(string $label, string $value, ?string $color): void
    {
        $this->label = $label;
        $this->value = $value;
        $this->color = $color;
    }

    public function updateOrder(int $order): void
    {
        $this->order = $order;
    }

    // Getters
    public function id(): ?int
    {
        return $this->id;
    }

    public function fieldId(): int
    {
        return $this->fieldId;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function color(): ?string
    {
        return $this->color;
    }

    public function isDefault(): bool
    {
        return $this->isDefault;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function order(): int
    {
        return $this->order;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }
}

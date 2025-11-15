<?php

declare(strict_types=1);

namespace App\Domain\Modules\Entities;

use DateTimeImmutable;

final class ModuleRecord
{
    public function __construct(
        private ?int $id,
        private int $moduleId,
        private array $data,
        private ?int $createdBy = null,
        private ?int $updatedBy = null,
        private ?DateTimeImmutable $createdAt = null,
        private ?DateTimeImmutable $updatedAt = null,
        private ?DateTimeImmutable $deletedAt = null,
    ) {}

    public static function create(
        int $moduleId,
        array $data,
        ?int $createdBy = null
    ): self {
        return new self(
            id: null,
            moduleId: $moduleId,
            data: $data,
            createdBy: $createdBy,
            createdAt: new DateTimeImmutable,
        );
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function moduleId(): int
    {
        return $this->moduleId;
    }

    public function data(): array
    {
        return $this->data;
    }

    public function getFieldValue(string $fieldName): mixed
    {
        return $this->data[$fieldName] ?? null;
    }

    public function createdBy(): ?int
    {
        return $this->createdBy;
    }

    public function updatedBy(): ?int
    {
        return $this->updatedBy;
    }

    public function createdAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function deletedAt(): ?DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function updateData(array $data, ?int $updatedBy = null): void
    {
        $this->data = $data;
        $this->updatedBy = $updatedBy;
    }

    public function updateFieldValue(string $fieldName, mixed $value, ?int $updatedBy = null): void
    {
        $this->data[$fieldName] = $value;
        $this->updatedBy = $updatedBy;
    }

    public function softDelete(): void
    {
        $this->deletedAt = new DateTimeImmutable;
    }

    public function restore(): void
    {
        $this->deletedAt = null;
    }

    public function isDeleted(): bool
    {
        return $this->deletedAt !== null;
    }
}

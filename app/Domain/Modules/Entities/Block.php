<?php

declare(strict_types=1);

namespace App\Domain\Modules\Entities;

use App\Domain\Modules\ValueObjects\BlockType;
use DateTimeImmutable;

final class Block
{
    private array $fields = [];

    public function __construct(
        private ?int $id,
        private int $moduleId,
        private string $name,
        private BlockType $type,
        private int $order,
        private int $columns,
        private bool $isCollapsible,
        private bool $isCollapsedByDefault,
        private DateTimeImmutable $createdAt,
        private ?DateTimeImmutable $updatedAt = null,
    ) {}

    public static function create(
        int $moduleId,
        string $name,
        BlockType $type = BlockType::SECTION,
        int $order = 0,
        int $columns = 2,
        bool $isCollapsible = false,
        bool $isCollapsedByDefault = false
    ): self {
        return new self(
            id: null,
            moduleId: $moduleId,
            name: $name,
            type: $type,
            order: $order,
            columns: $columns,
            isCollapsible: $isCollapsible,
            isCollapsedByDefault: $isCollapsedByDefault,
            createdAt: new DateTimeImmutable,
        );
    }

    public function addField(Field $field): void
    {
        $this->fields[] = $field;
    }

    public function updateDetails(
        string $name,
        BlockType $type,
        int $columns,
        bool $isCollapsible,
        bool $isCollapsedByDefault
    ): void {
        $this->name = $name;
        $this->type = $type;
        $this->columns = $columns;
        $this->isCollapsible = $isCollapsible;
        $this->isCollapsedByDefault = $isCollapsedByDefault;
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

    public function moduleId(): int
    {
        return $this->moduleId;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function type(): BlockType
    {
        return $this->type;
    }

    public function order(): int
    {
        return $this->order;
    }

    public function columns(): int
    {
        return $this->columns;
    }

    public function isCollapsible(): bool
    {
        return $this->isCollapsible;
    }

    public function isCollapsedByDefault(): bool
    {
        return $this->isCollapsedByDefault;
    }

    public function fields(): array
    {
        return $this->fields;
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

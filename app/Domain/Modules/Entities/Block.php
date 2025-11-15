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
        private string $label,
        private BlockType $type,
        private int $order,
        private array $settings,
        private DateTimeImmutable $createdAt,
        private ?DateTimeImmutable $updatedAt = null,
    ) {}

    public static function create(
        int $moduleId,
        string $label,
        BlockType $type = BlockType::SECTION,
        int $order = 0,
        array $settings = []
    ): self {
        return new self(
            id: null,
            moduleId: $moduleId,
            label: $label,
            type: $type,
            order: $order,
            settings: $settings,
            createdAt: new DateTimeImmutable,
        );
    }

    public function addField(Field $field): void
    {
        $this->fields[] = $field;
    }

    public function updateDetails(
        string $label,
        BlockType $type,
        array $settings
    ): void {
        $this->label = $label;
        $this->type = $type;
        $this->settings = $settings;
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

    public function label(): string
    {
        return $this->label;
    }

    public function type(): BlockType
    {
        return $this->type;
    }

    public function order(): int
    {
        return $this->order;
    }

    public function settings(): array
    {
        return $this->settings;
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

<?php

declare(strict_types=1);

namespace App\Domain\Modules\Entities;

use App\Domain\Modules\ValueObjects\ModuleSettings;
use DateTimeImmutable;

final class Module
{
    private array $blocks = [];

    private array $fields = [];

    public function __construct(
        private ?int $id,
        private string $name,
        private string $singularName,
        private ?string $icon,
        private ?string $description,
        private bool $isActive,
        private ModuleSettings $settings,
        private int $order,
        private DateTimeImmutable $createdAt,
        private ?DateTimeImmutable $updatedAt = null,
        private ?DateTimeImmutable $deletedAt = null,
    ) {}

    public static function create(
        string $name,
        string $singularName,
        ?string $icon = null,
        ?string $description = null,
        ?ModuleSettings $settings = null,
        int $order = 0
    ): self {
        return new self(
            id: null,
            name: $name,
            singularName: $singularName,
            icon: $icon,
            description: $description,
            isActive: true,
            settings: $settings ?? ModuleSettings::default(),
            order: $order,
            createdAt: new DateTimeImmutable,
        );
    }

    public function addBlock(Block $block): void
    {
        $this->blocks[] = $block;
    }

    public function addField(Field $field): void
    {
        $this->fields[] = $field;
    }

    public function activate(): void
    {
        $this->isActive = true;
    }

    public function deactivate(): void
    {
        $this->isActive = false;
    }

    public function updateDetails(
        string $name,
        string $singularName,
        ?string $icon,
        ?string $description
    ): void {
        $this->name = $name;
        $this->singularName = $singularName;
        $this->icon = $icon;
        $this->description = $description;
    }

    public function updateSettings(ModuleSettings $settings): void
    {
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

    public function name(): string
    {
        return $this->name;
    }

    public function singularName(): string
    {
        return $this->singularName;
    }

    public function icon(): ?string
    {
        return $this->icon;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function settings(): ModuleSettings
    {
        return $this->settings;
    }

    public function order(): int
    {
        return $this->order;
    }

    public function blocks(): array
    {
        return $this->blocks;
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

    public function deletedAt(): ?DateTimeImmutable
    {
        return $this->deletedAt;
    }
}

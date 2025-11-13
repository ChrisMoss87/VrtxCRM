<?php

declare(strict_types=1);

namespace App\Domain\Modules\Entities;

use App\Domain\Modules\ValueObjects\RelationshipSettings;
use App\Domain\Modules\ValueObjects\RelationshipType;
use DateTimeImmutable;
use DomainException;

/**
 * Relationship Entity
 *
 * Represents a relationship between two modules in the CRM system.
 * Uses hexagonal architecture - framework-agnostic domain entity.
 */
final class Relationship
{
    public function __construct(
        private readonly int $id,
        private readonly int $fromModuleId,
        private readonly int $toModuleId,
        private readonly string $name,
        private readonly string $apiName,
        private readonly RelationshipType $type,
        private readonly RelationshipSettings $settings,
        private readonly DateTimeImmutable $createdAt,
        private readonly ?DateTimeImmutable $updatedAt = null,
    ) {
        $this->validateRelationship();
    }

    /**
     * Create a new one-to-many relationship.
     */
    public static function createOneToMany(
        int $id,
        int $fromModuleId,
        int $toModuleId,
        string $name,
        string $apiName,
        RelationshipSettings $settings,
    ): self {
        return new self(
            id: $id,
            fromModuleId: $fromModuleId,
            toModuleId: $toModuleId,
            name: $name,
            apiName: $apiName,
            type: RelationshipType::oneToMany(),
            settings: $settings,
            createdAt: new DateTimeImmutable(),
        );
    }

    /**
     * Create a new many-to-many relationship.
     */
    public static function createManyToMany(
        int $id,
        int $fromModuleId,
        int $toModuleId,
        string $name,
        string $apiName,
        RelationshipSettings $settings,
    ): self {
        return new self(
            id: $id,
            fromModuleId: $fromModuleId,
            toModuleId: $toModuleId,
            name: $name,
            apiName: $apiName,
            type: RelationshipType::manyToMany(),
            settings: $settings,
            createdAt: new DateTimeImmutable(),
        );
    }

    /**
     * Check if this relationship supports cascade delete.
     */
    public function shouldCascadeDelete(): bool
    {
        return $this->settings->cascadeDelete();
    }

    /**
     * Check if this relationship is required (records must be linked).
     */
    public function isRequired(): bool
    {
        return $this->settings->isRequired();
    }

    /**
     * Check if users can create related records inline.
     */
    public function allowsInlineCreation(): bool
    {
        return $this->settings->allowCreateRelated();
    }

    /**
     * Get the field to display in the lookup selector.
     */
    public function getDisplayField(): string
    {
        return $this->settings->displayField();
    }

    /**
     * Get the field to sort related records by.
     */
    public function getSortField(): string
    {
        return $this->settings->sortField();
    }

    /**
     * Get the sort direction for related records.
     */
    public function getSortDirection(): string
    {
        return $this->settings->sortDirection();
    }

    /**
     * Check if this is a one-to-many relationship.
     */
    public function isOneToMany(): bool
    {
        return $this->type->equals(RelationshipType::oneToMany());
    }

    /**
     * Check if this is a many-to-many relationship.
     */
    public function isManyToMany(): bool
    {
        return $this->type->equals(RelationshipType::manyToMany());
    }

    // Getters
    public function id(): int
    {
        return $this->id;
    }

    public function fromModuleId(): int
    {
        return $this->fromModuleId;
    }

    public function toModuleId(): int
    {
        return $this->toModuleId;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function apiName(): string
    {
        return $this->apiName;
    }

    public function type(): RelationshipType
    {
        return $this->type;
    }

    public function settings(): RelationshipSettings
    {
        return $this->settings;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * Validate the relationship configuration.
     */
    private function validateRelationship(): void
    {
        if ($this->fromModuleId === $this->toModuleId) {
            throw new DomainException('A module cannot have a relationship with itself');
        }

        if (empty($this->name)) {
            throw new DomainException('Relationship name cannot be empty');
        }

        if (empty($this->apiName)) {
            throw new DomainException('Relationship API name cannot be empty');
        }

        if (! preg_match('/^[a-z_][a-z0-9_]*$/', $this->apiName)) {
            throw new DomainException('Relationship API name must be snake_case');
        }
    }
}

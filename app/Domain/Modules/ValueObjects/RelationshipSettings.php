<?php

declare(strict_types=1);

namespace App\Domain\Modules\ValueObjects;

use InvalidArgumentException;

/**
 * Relationship Settings Value Object
 *
 * Encapsulates all settings for a relationship between modules.
 */
final readonly class RelationshipSettings
{
    public function __construct(
        private bool $cascadeDelete = false,
        private bool $required = false,
        private bool $allowCreateRelated = true,
        private string $displayField = 'name',
        private string $sortField = 'created_at',
        private string $sortDirection = 'desc',
        private ?array $filters = null,
    ) {
        $this->validate();
    }

    public static function fromArray(array $data): self
    {
        return new self(
            cascadeDelete: $data['cascade_delete'] ?? false,
            required: $data['required'] ?? false,
            allowCreateRelated: $data['allow_create_related'] ?? true,
            displayField: $data['display_field'] ?? 'name',
            sortField: $data['sort_field'] ?? 'created_at',
            sortDirection: $data['sort_direction'] ?? 'desc',
            filters: $data['filters'] ?? null,
        );
    }

    public static function defaults(): self
    {
        return new self();
    }

    public function cascadeDelete(): bool
    {
        return $this->cascadeDelete;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function allowCreateRelated(): bool
    {
        return $this->allowCreateRelated;
    }

    public function displayField(): string
    {
        return $this->displayField;
    }

    public function sortField(): string
    {
        return $this->sortField;
    }

    public function sortDirection(): string
    {
        return $this->sortDirection;
    }

    public function filters(): ?array
    {
        return $this->filters;
    }

    public function toArray(): array
    {
        return [
            'cascade_delete' => $this->cascadeDelete,
            'required' => $this->required,
            'allow_create_related' => $this->allowCreateRelated,
            'display_field' => $this->displayField,
            'sort_field' => $this->sortField,
            'sort_direction' => $this->sortDirection,
            'filters' => $this->filters,
        ];
    }

    private function validate(): void
    {
        if (empty($this->displayField)) {
            throw new InvalidArgumentException('Display field cannot be empty');
        }

        if (empty($this->sortField)) {
            throw new InvalidArgumentException('Sort field cannot be empty');
        }

        if (! in_array($this->sortDirection, ['asc', 'desc'], true)) {
            throw new InvalidArgumentException('Sort direction must be asc or desc');
        }

        if ($this->filters !== null && ! is_array($this->filters)) {
            throw new InvalidArgumentException('Filters must be an array');
        }
    }
}

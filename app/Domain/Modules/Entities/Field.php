<?php

declare(strict_types=1);

namespace App\Domain\Modules\Entities;

use App\Domain\Modules\ValueObjects\FieldSettings;
use App\Domain\Modules\ValueObjects\FieldType;
use App\Domain\Modules\ValueObjects\ValidationRules;
use DateTimeImmutable;

final class Field
{
    private array $options = [];

    public function __construct(
        private ?int $id,
        private int $moduleId,
        private ?int $blockId,
        private string $name,
        private string $apiName,
        private FieldType $type,
        private ?string $description,
        private ?string $helpText,
        private bool $isRequired,
        private bool $isUnique,
        private bool $isSearchable,
        private bool $isVisibleInList,
        private bool $isVisibleInDetail,
        private ValidationRules $validationRules,
        private FieldSettings $settings,
        private ?string $defaultValue,
        private int $order,
        private int $width,
        private DateTimeImmutable $createdAt,
        private ?DateTimeImmutable $updatedAt = null,
    ) {}

    public static function create(
        int $moduleId,
        ?int $blockId,
        string $name,
        string $apiName,
        FieldType $type,
        ?string $description = null,
        ?string $helpText = null,
        bool $isRequired = false,
        bool $isUnique = false,
        ?ValidationRules $validationRules = null,
        ?FieldSettings $settings = null,
        ?string $defaultValue = null,
        int $order = 0,
        int $width = 50
    ): self {
        return new self(
            id: null,
            moduleId: $moduleId,
            blockId: $blockId,
            name: $name,
            apiName: $apiName,
            type: $type,
            description: $description,
            helpText: $helpText,
            isRequired: $isRequired,
            isUnique: $isUnique,
            isSearchable: true,
            isVisibleInList: true,
            isVisibleInDetail: true,
            validationRules: $validationRules ?? ValidationRules::empty(),
            settings: $settings ?? FieldSettings::default(),
            defaultValue: $defaultValue,
            order: $order,
            width: $width,
            createdAt: new DateTimeImmutable,
        );
    }

    public function addOption(FieldOption $option): void
    {
        $this->options[] = $option;
    }

    public function updateDetails(
        string $name,
        string $apiName,
        FieldType $type,
        ?string $description,
        ?string $helpText
    ): void {
        $this->name = $name;
        $this->apiName = $apiName;
        $this->type = $type;
        $this->description = $description;
        $this->helpText = $helpText;
    }

    public function updateValidation(
        bool $isRequired,
        bool $isUnique,
        ValidationRules $rules
    ): void {
        $this->isRequired = $isRequired;
        $this->isUnique = $isUnique;
        $this->validationRules = $rules;
    }

    public function updateVisibility(
        bool $isVisibleInList,
        bool $isVisibleInDetail,
        bool $isSearchable
    ): void {
        $this->isVisibleInList = $isVisibleInList;
        $this->isVisibleInDetail = $isVisibleInDetail;
        $this->isSearchable = $isSearchable;
    }

    public function updateSettings(FieldSettings $settings): void
    {
        $this->settings = $settings;
    }

    public function updateLayout(int $order, int $width): void
    {
        $this->order = $order;
        $this->width = $width;
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

    public function blockId(): ?int
    {
        return $this->blockId;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function apiName(): string
    {
        return $this->apiName;
    }

    public function type(): FieldType
    {
        return $this->type;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function helpText(): ?string
    {
        return $this->helpText;
    }

    public function isRequired(): bool
    {
        return $this->isRequired;
    }

    public function isUnique(): bool
    {
        return $this->isUnique;
    }

    public function isSearchable(): bool
    {
        return $this->isSearchable;
    }

    public function isVisibleInList(): bool
    {
        return $this->isVisibleInList;
    }

    public function isVisibleInDetail(): bool
    {
        return $this->isVisibleInDetail;
    }

    public function validationRules(): ValidationRules
    {
        return $this->validationRules;
    }

    public function settings(): FieldSettings
    {
        return $this->settings;
    }

    public function defaultValue(): ?string
    {
        return $this->defaultValue;
    }

    public function order(): int
    {
        return $this->order;
    }

    public function width(): int
    {
        return $this->width;
    }

    public function options(): array
    {
        return $this->options;
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
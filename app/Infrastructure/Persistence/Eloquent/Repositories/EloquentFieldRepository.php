<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Modules\Entities\Field;
use App\Domain\Modules\Repositories\FieldRepositoryInterface;
use App\Domain\Modules\ValueObjects\FieldSettings;
use App\Domain\Modules\ValueObjects\FieldType;
use App\Domain\Modules\ValueObjects\ValidationRules;
use App\Infrastructure\Persistence\Eloquent\Models\FieldModel;
use DateTimeImmutable;

final class EloquentFieldRepository implements FieldRepositoryInterface
{
    public function findById(int $id): ?Field
    {
        $model = FieldModel::with('options')->find($id);

        return $model ? $this->toDomain($model) : null;
    }

    public function findByModuleId(int $moduleId): array
    {
        // Fields don't have module_id, they belong to blocks
        // This method may not be needed or should query through blocks
        return [];
    }

    public function findByBlockId(int $blockId): array
    {
        return FieldModel::with('options')
            ->where('block_id', $blockId)
            ->orderBy('order')
            ->get()
            ->map(fn (FieldModel $model): Field => $this->toDomain($model))
            ->all();
    }

    public function save(Field $field): Field
    {
        $data = [
            'block_id' => $field->blockId(),
            'label' => $field->label(),
            'api_name' => $field->apiName(),
            'type' => $field->type()->value,
            'description' => $field->description(),
            'help_text' => $field->helpText(),
            'is_required' => $field->isRequired(),
            'is_unique' => $field->isUnique(),
            'is_searchable' => $field->isSearchable(),
            'validation_rules' => $field->validationRules()->jsonSerialize(),
            'settings' => $field->settings()->jsonSerialize(),
            'default_value' => $field->defaultValue(),
            'order' => $field->order(),
            'width' => $field->width(),
        ];

        if ($field->id() === null) {
            $model = FieldModel::create($data);
        } else {
            $model = FieldModel::findOrFail($field->id());
            $model->update($data);
        }

        return $this->toDomain($model->load('options'));
    }

    public function delete(int $id): bool
    {
        return (bool) FieldModel::destroy($id);
    }

    public function existsByApiName(int $moduleId, string $apiName, ?int $excludeId = null): bool
    {
        // Since fields don't have module_id directly, we need to join through blocks
        $query = FieldModel::whereHas('block', function ($q) use ($moduleId) {
            $q->where('module_id', $moduleId);
        })
            ->where('api_name', $apiName);

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    public function toDomain(FieldModel $model): Field
    {
        $field = new Field(
            id: $model->id,
            blockId: $model->block_id,
            label: $model->label,
            apiName: $model->api_name,
            type: FieldType::from($model->type),
            description: $model->description,
            helpText: $model->help_text,
            isRequired: $model->is_required,
            isUnique: $model->is_unique,
            isSearchable: $model->is_searchable,
            validationRules: ValidationRules::fromArray($model->validation_rules ?? []),
            settings: FieldSettings::fromArray($model->settings ?? []),
            defaultValue: $model->default_value,
            order: $model->order,
            width: $model->width,
            createdAt: new DateTimeImmutable($model->created_at->toDateTimeString()),
            updatedAt: $model->updated_at ? new DateTimeImmutable($model->updated_at->toDateTimeString()) : null,
        );

        if ($model->relationLoaded('options')) {
            $optionRepo = new EloquentFieldOptionRepository;
            foreach ($model->options as $optionModel) {
                $field->addOption($optionRepo->toDomain($optionModel));
            }
        }

        return $field;
    }
}

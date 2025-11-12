<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Modules\Entities\Module;
use App\Domain\Modules\Repositories\ModuleRepositoryInterface;
use App\Domain\Modules\ValueObjects\ModuleSettings;
use App\Infrastructure\Persistence\Eloquent\Models\ModuleModel;
use DateTimeImmutable;

final class EloquentModuleRepository implements ModuleRepositoryInterface
{
    public function findById(int $id): ?Module
    {
        $model = ModuleModel::with(['blocks.fields.options', 'fields.options'])
            ->find($id);

        return $model ? $this->toDomain($model) : null;
    }

    public function findAll(): array
    {
        return ModuleModel::with(['blocks.fields.options', 'fields.options'])
            ->orderBy('order')
            ->get()
            ->map(fn (ModuleModel $model): Module => $this->toDomain($model))
            ->all();
    }

    public function findActive(): array
    {
        return ModuleModel::with(['blocks.fields.options', 'fields.options'])
            ->where('is_active', true)
            ->orderBy('order')
            ->get()
            ->map(fn (ModuleModel $model): Module => $this->toDomain($model))
            ->all();
    }

    public function save(Module $module): Module
    {
        $data = [
            'name' => $module->name(),
            'singular_name' => $module->singularName(),
            'icon' => $module->icon(),
            'description' => $module->description(),
            'is_active' => $module->isActive(),
            'settings' => $module->settings()->jsonSerialize(),
            'order' => $module->order(),
        ];

        if ($module->id() === null) {
            $model = ModuleModel::create($data);
        } else {
            $model = ModuleModel::findOrFail($module->id());
            $model->update($data);
        }

        return $this->toDomain($model->load(['blocks.fields.options', 'fields.options']));
    }

    public function delete(int $id): bool
    {
        return (bool) ModuleModel::destroy($id);
    }

    public function existsByName(string $name, ?int $excludeId = null): bool
    {
        $query = ModuleModel::where('name', $name);

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    private function toDomain(ModuleModel $model): Module
    {
        $module = new Module(
            id: $model->id,
            name: $model->name,
            singularName: $model->singular_name,
            icon: $model->icon,
            description: $model->description,
            isActive: $model->is_active,
            settings: ModuleSettings::fromArray($model->settings ?? []),
            order: $model->order,
            createdAt: new DateTimeImmutable($model->created_at->toDateTimeString()),
            updatedAt: $model->updated_at ? new DateTimeImmutable($model->updated_at->toDateTimeString()) : null,
            deletedAt: $model->deleted_at ? new DateTimeImmutable($model->deleted_at->toDateTimeString()) : null,
        );

        // Load blocks and fields if present
        if ($model->relationLoaded('blocks')) {
            foreach ($model->blocks as $blockModel) {
                $module->addBlock((new EloquentBlockRepository)->toDomain($blockModel));
            }
        }

        if ($model->relationLoaded('fields')) {
            foreach ($model->fields as $fieldModel) {
                $module->addField((new EloquentFieldRepository)->toDomain($fieldModel));
            }
        }

        return $module;
    }
}
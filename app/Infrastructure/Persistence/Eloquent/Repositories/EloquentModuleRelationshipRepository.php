<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Modules\Entities\Relationship;
use App\Domain\Modules\Repositories\ModuleRelationshipRepositoryInterface;
use App\Domain\Modules\ValueObjects\RelationshipSettings;
use App\Domain\Modules\ValueObjects\RelationshipType;
use App\Infrastructure\Persistence\Eloquent\Models\ModuleRelationshipModel;
use DateTimeImmutable;

/**
 * Eloquent Module Relationship Repository
 *
 * Maps between domain entities and Eloquent models.
 * This is an adapter in hexagonal architecture.
 */
final class EloquentModuleRelationshipRepository implements ModuleRelationshipRepositoryInterface
{
    public function findById(int $id): ?Relationship
    {
        $model = ModuleRelationshipModel::find($id);

        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByApiName(string $apiName): ?Relationship
    {
        $model = ModuleRelationshipModel::where('api_name', $apiName)->first();

        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByFromModule(int $moduleId): array
    {
        $models = ModuleRelationshipModel::where('from_module_id', $moduleId)
            ->orderBy('name')
            ->get();

        return $models->map(fn ($model) => $this->toDomainEntity($model))->all();
    }

    public function findByToModule(int $moduleId): array
    {
        $models = ModuleRelationshipModel::where('to_module_id', $moduleId)
            ->orderBy('name')
            ->get();

        return $models->map(fn ($model) => $this->toDomainEntity($model))->all();
    }

    public function findAllForModule(int $moduleId): array
    {
        $models = ModuleRelationshipModel::where('from_module_id', $moduleId)
            ->orWhere('to_module_id', $moduleId)
            ->orderBy('name')
            ->get();

        return $models->map(fn ($model) => $this->toDomainEntity($model))->all();
    }

    public function save(Relationship $relationship): Relationship
    {
        $model = ModuleRelationshipModel::updateOrCreate(
            ['id' => $relationship->id()],
            [
                'from_module_id' => $relationship->fromModuleId(),
                'to_module_id' => $relationship->toModuleId(),
                'name' => $relationship->name(),
                'api_name' => $relationship->apiName(),
                'type' => $relationship->type()->toString(),
                'settings' => $relationship->settings()->toArray(),
            ]
        );

        return $this->toDomainEntity($model);
    }

    public function delete(int $id): bool
    {
        return ModuleRelationshipModel::destroy($id) > 0;
    }

    public function existsBetweenModules(int $fromModuleId, int $toModuleId, string $apiName): bool
    {
        return ModuleRelationshipModel::where('from_module_id', $fromModuleId)
            ->where('to_module_id', $toModuleId)
            ->where('api_name', $apiName)
            ->exists();
    }

    /**
     * Convert Eloquent model to domain entity.
     */
    private function toDomainEntity(ModuleRelationshipModel $model): Relationship
    {
        return new Relationship(
            id: $model->id,
            fromModuleId: $model->from_module_id,
            toModuleId: $model->to_module_id,
            name: $model->name,
            apiName: $model->api_name,
            type: RelationshipType::fromString($model->type),
            settings: RelationshipSettings::fromArray($model->settings ?? []),
            createdAt: new DateTimeImmutable($model->created_at->toDateTimeString()),
            updatedAt: $model->updated_at ? new DateTimeImmutable($model->updated_at->toDateTimeString()) : null,
        );
    }
}

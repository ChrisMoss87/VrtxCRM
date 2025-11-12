<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Modules\Entities\Block;
use App\Domain\Modules\ValueObjects\BlockType;
use App\Infrastructure\Persistence\Eloquent\Models\BlockModel;
use DateTimeImmutable;

final class EloquentBlockRepository
{
    public function toDomain(BlockModel $model): Block
    {
        $block = new Block(
            id: $model->id,
            moduleId: $model->module_id,
            name: $model->name,
            type: BlockType::from($model->type),
            order: $model->order,
            columns: $model->columns,
            isCollapsible: $model->is_collapsible,
            isCollapsedByDefault: $model->is_collapsed_by_default,
            createdAt: new DateTimeImmutable($model->created_at->toDateTimeString()),
            updatedAt: $model->updated_at ? new DateTimeImmutable($model->updated_at->toDateTimeString()) : null,
        );

        if ($model->relationLoaded('fields')) {
            $fieldRepo = new EloquentFieldRepository;
            foreach ($model->fields as $fieldModel) {
                $block->addField($fieldRepo->toDomain($fieldModel));
            }
        }

        return $block;
    }
}
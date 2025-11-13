<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Modules\Entities\FieldOption;
use App\Infrastructure\Persistence\Eloquent\Models\FieldOptionModel;
use DateTimeImmutable;

final class EloquentFieldOptionRepository
{
    public function toDomain(FieldOptionModel $model): FieldOption
    {
        return new FieldOption(
            id: $model->id,
            fieldId: $model->field_id,
            label: $model->label,
            value: $model->value,
            color: $model->color,
            isDefault: $model->is_default,
            isActive: $model->is_active,
            order: $model->order,
            createdAt: new DateTimeImmutable($model->created_at->toDateTimeString()),
            updatedAt: $model->updated_at ? new DateTimeImmutable($model->updated_at->toDateTimeString()) : null,
        );
    }
}

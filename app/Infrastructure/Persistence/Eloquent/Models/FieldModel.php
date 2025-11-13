<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class FieldModel extends Model
{
    use HasFactory;

    protected $table = 'fields';

    protected $fillable = [
        'block_id',
        'relationship_id',
        'type',
        'api_name',
        'label',
        'description',
        'help_text',
        'is_required',
        'is_unique',
        'is_searchable',
        'order',
        'default_value',
        'validation_rules',
        'settings',
        'width',
    ];

    protected $casts = [
        'block_id' => 'integer',
        'relationship_id' => 'integer',
        'is_required' => 'boolean',
        'is_unique' => 'boolean',
        'is_searchable' => 'boolean',
        'validation_rules' => 'array',
        'settings' => 'array',
        'order' => 'integer',
        'width' => 'integer',
    ];

    public function block(): BelongsTo
    {
        return $this->belongsTo(BlockModel::class, 'block_id');
    }

    public function relationship(): BelongsTo
    {
        return $this->belongsTo(ModuleRelationshipModel::class, 'relationship_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(FieldOptionModel::class, 'field_id');
    }
}

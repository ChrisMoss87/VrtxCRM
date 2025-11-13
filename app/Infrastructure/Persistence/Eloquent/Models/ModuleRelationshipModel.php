<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Module Relationship Eloquent Model
 *
 * Represents a relationship between two modules in the database.
 * This is the infrastructure layer - responsible for database persistence.
 */
final class ModuleRelationshipModel extends Model
{
    use HasFactory;

    protected $table = 'module_relationships';

    protected $fillable = [
        'from_module_id',
        'to_module_id',
        'name',
        'api_name',
        'type',
        'settings',
    ];

    protected $casts = [
        'from_module_id' => 'integer',
        'to_module_id' => 'integer',
        'settings' => 'array',
    ];

    /**
     * Get the "from" module (the module that owns the relationship).
     */
    public function fromModule(): BelongsTo
    {
        return $this->belongsTo(ModuleModel::class, 'from_module_id');
    }

    /**
     * Get the "to" module (the module being related to).
     */
    public function toModule(): BelongsTo
    {
        return $this->belongsTo(ModuleModel::class, 'to_module_id');
    }
}

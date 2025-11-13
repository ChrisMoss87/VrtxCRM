<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class ModuleModel extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'modules';

    protected $fillable = [
        'name',
        'singular_name',
        'api_name',
        'icon',
        'description',
        'is_active',
        'is_system',
        'settings',
        'order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_system' => 'boolean',
        'settings' => 'array',
        'order' => 'integer',
    ];

    public function blocks(): HasMany
    {
        return $this->hasMany(BlockModel::class, 'module_id');
    }

    public function records(): HasMany
    {
        return $this->hasMany(ModuleRecordModel::class, 'module_id');
    }

    /**
     * Get all fields through blocks.
     */
    public function fields()
    {
        return $this->hasManyThrough(FieldModel::class, BlockModel::class, 'module_id', 'block_id');
    }
}

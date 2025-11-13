<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class TableView extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'module',
        'description',
        'filters',
        'sorting',
        'column_visibility',
        'column_order',
        'column_widths',
        'page_size',
        'is_default',
        'is_public',
    ];

    protected $casts = [
        'filters' => 'array',
        'sorting' => 'array',
        'column_visibility' => 'array',
        'column_order' => 'array',
        'column_widths' => 'array',
        'page_size' => 'integer',
        'is_default' => 'boolean',
        'is_public' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shares(): HasMany
    {
        return $this->hasMany(TableViewShare::class);
    }

    public function scopeForModule($query, string $module)
    {
        return $query->where('module', $module);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeAccessibleBy($query, int $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('user_id', $userId)
                ->orWhere('is_public', true)
                ->orWhereHas('shares', function ($sq) use ($userId) {
                    $sq->where('user_id', $userId);
                });
        });
    }
}

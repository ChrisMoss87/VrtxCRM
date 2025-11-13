<?php

declare(strict_types=1);

namespace App\Models\Tenancy;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class TenantSetting extends Model
{
    use HasFactory;

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'tenant_id',
        'key',
        'value',
        'type',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'value' => 'json',
    ];

    /**
     * Get the tenant that owns the setting.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Scope a query to only include settings of a given key.
     */
    public function scopeForKey($query, string $key)
    {
        return $query->where('key', $key);
    }
}

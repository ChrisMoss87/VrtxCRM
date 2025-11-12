<?php

declare(strict_types=1);

namespace App\Models\Tenancy;

use Stancl\Tenancy\Database\Models\Domain as BaseDomain;

final class Domain extends BaseDomain
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'domain',
        'tenant_id',
        'is_primary',
        'is_fallback',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'is_primary' => 'boolean',
        'is_fallback' => 'boolean',
    ];

    /**
     * The default values for attributes.
     */
    protected $attributes = [
        'is_primary' => false,
        'is_fallback' => false,
    ];

    /**
     * Mark this domain as primary for the tenant.
     */
    public function markAsPrimary(): void
    {
        // Remove primary flag from other domains
        static::where('tenant_id', $this->tenant_id)
            ->where('id', '!=', $this->id)
            ->update(['is_primary' => false]);

        // Set this domain as primary
        $this->update(['is_primary' => true]);
    }

    /**
     * Check if this is the primary domain.
     */
    public function isPrimary(): bool
    {
        return $this->is_primary;
    }
}

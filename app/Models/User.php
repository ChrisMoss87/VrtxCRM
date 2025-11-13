<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

final class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'preferences',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'preferences' => 'array',
        ];
    }

    /**
     * Get the default view ID for a specific module.
     */
    public function getDefaultViewForModule(string $module): ?int
    {
        return $this->preferences['default_views'][$module] ?? null;
    }

    /**
     * Set the default view for a specific module.
     */
    public function setDefaultViewForModule(string $module, int $viewId): void
    {
        $preferences = $this->preferences ?? [];
        $preferences['default_views'][$module] = $viewId;
        $this->preferences = $preferences;
        $this->save();
    }

    /**
     * Clear the default view for a specific module.
     */
    public function clearDefaultViewForModule(string $module): void
    {
        $preferences = $this->preferences ?? [];
        unset($preferences['default_views'][$module]);
        $this->preferences = $preferences;
        $this->save();
    }
}

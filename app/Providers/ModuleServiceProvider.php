<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Modules\Repositories\FieldRepositoryInterface;
use App\Domain\Modules\Repositories\ModuleRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentFieldRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentModuleRepository;
use Illuminate\Support\ServiceProvider;

final class ModuleServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind repository interfaces to implementations
        $this->app->bind(ModuleRepositoryInterface::class, EloquentModuleRepository::class);
        $this->app->bind(FieldRepositoryInterface::class, EloquentFieldRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}

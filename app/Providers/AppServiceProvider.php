<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Modules\Repositories\ModuleRelationshipRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentModuleRelationshipRepository;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind repository interfaces to implementations
        $this->app->bind(
            ModuleRelationshipRepositoryInterface::class,
            EloquentModuleRelationshipRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}

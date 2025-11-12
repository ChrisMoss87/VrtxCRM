<?php

declare(strict_types=1);

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomainOrSubdomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenancyServiceProvider and apply to
| all tenant subdomains (e.g., acme.vrtxcrm.local, startup.vrtxcrm.local).
|
| All routes here are automatically scoped to the current tenant's database.
|
*/

Route::middleware([
    'web',
    InitializeTenancyByDomainOrSubdomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    // Auth routes (login, register, password reset, etc.)
    // MUST be inside tenant middleware to authenticate against tenant database
    require __DIR__.'/auth.php';

    // Tenant dashboard - require authentication
    Route::middleware('auth')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Settings routes
        require __DIR__.'/settings.php';
    });
});

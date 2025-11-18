<?php

declare(strict_types=1);

use App\Http\Controllers\Api\ModuleController;
use App\Http\Controllers\Api\ModuleRecordController;
use App\Http\Controllers\Api\TableViewController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ModuleViewController;
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

        // Admin Module Builder routes
        Route::prefix('admin/modules')->name('admin.modules.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\ModuleBuilderController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\Admin\ModuleBuilderController::class, 'create'])->name('create');
            Route::get('/{id}/edit', [App\Http\Controllers\Admin\ModuleBuilderController::class, 'edit'])->name('edit');
        });

        // Module web routes (Inertia views)
        Route::prefix('modules/{moduleApiName}')->group(function () {
            Route::get('/', [ModuleViewController::class, 'index'])->name('modules.index');
            Route::get('/create', [ModuleViewController::class, 'create'])->name('modules.create');
            Route::get('/{id}', [ModuleViewController::class, 'show'])->name('modules.show');
            Route::get('/{id}/edit', [ModuleViewController::class, 'edit'])->name('modules.edit');
        });

        // API routes for modules and records
        Route::prefix('api')->group(function () {
            // Module endpoints
            Route::get('modules', [ModuleController::class, 'index'])->name('api.modules.index');
            Route::get('modules/{apiName}', [ModuleController::class, 'show'])->name('api.modules.show');

            // Module relationship endpoints
            Route::get('relationships', [App\Http\Controllers\Api\ModuleRelationshipController::class, 'index'])->name('api.relationships.index');
            Route::post('relationships', [App\Http\Controllers\Api\ModuleRelationshipController::class, 'store'])->name('api.relationships.store');
            Route::get('relationships/{id}', [App\Http\Controllers\Api\ModuleRelationshipController::class, 'show'])->name('api.relationships.show');
            Route::put('relationships/{id}', [App\Http\Controllers\Api\ModuleRelationshipController::class, 'update'])->name('api.relationships.update');
            Route::delete('relationships/{id}', [App\Http\Controllers\Api\ModuleRelationshipController::class, 'destroy'])->name('api.relationships.destroy');

            // Related records endpoints
            Route::get('relationships/{relationshipId}/available', [App\Http\Controllers\Api\RelatedRecordsController::class, 'available'])->name('api.relationships.available');
            Route::post('relationships/{relationshipId}/link', [App\Http\Controllers\Api\RelatedRecordsController::class, 'link'])->name('api.relationships.link');
            Route::post('relationships/{relationshipId}/unlink', [App\Http\Controllers\Api\RelatedRecordsController::class, 'unlink'])->name('api.relationships.unlink');

            // Module record endpoints
            Route::get('modules/{moduleApiName}/records', [ModuleRecordController::class, 'index'])->name('api.modules.records.index');
            Route::get('modules/{moduleApiName}/records/export', [ModuleRecordController::class, 'export'])->name('api.modules.records.export');
            Route::post('modules/{moduleApiName}/records', [ModuleRecordController::class, 'store'])->name('api.modules.records.store');
            Route::post('modules/{moduleApiName}/records/bulk-delete', [ModuleRecordController::class, 'bulkDelete'])->name('api.modules.records.bulk-delete');
            Route::post('modules/{moduleApiName}/records/bulk-update', [ModuleRecordController::class, 'bulkUpdate'])->name('api.modules.records.bulk-update');
            Route::get('modules/{moduleApiName}/records/{id}', [ModuleRecordController::class, 'show'])->name('api.modules.records.show');
            Route::get('modules/{moduleApiName}/records/{id}/related', [App\Http\Controllers\Api\RelatedRecordsController::class, 'index'])->name('api.modules.records.related');
            Route::put('modules/{moduleApiName}/records/{id}', [ModuleRecordController::class, 'update'])->name('api.modules.records.update');
            Route::delete('modules/{moduleApiName}/records/{id}', [ModuleRecordController::class, 'destroy'])->name('api.modules.records.destroy');

            // Table view endpoints
            Route::get('table-views', [TableViewController::class, 'index'])->name('api.table-views.index');
            Route::post('table-views', [TableViewController::class, 'store'])->name('api.table-views.store');
            Route::get('table-views/{tableView}', [TableViewController::class, 'show'])->name('api.table-views.show');
            Route::put('table-views/{tableView}', [TableViewController::class, 'update'])->name('api.table-views.update');
            Route::delete('table-views/{tableView}', [TableViewController::class, 'destroy'])->name('api.table-views.destroy');
            Route::post('table-views/{tableView}/duplicate', [TableViewController::class, 'duplicate'])->name('api.table-views.duplicate');

            // User preference endpoints
            Route::get('user/preferences', [App\Http\Controllers\Api\UserPreferenceController::class, 'show'])->name('api.user.preferences.show');
            Route::put('user/preferences', [App\Http\Controllers\Api\UserPreferenceController::class, 'update'])->name('api.user.preferences.update');
            Route::post('user/preferences/default-view', [App\Http\Controllers\Api\UserPreferenceController::class, 'setDefaultView'])->name('api.user.preferences.set-default-view');
            Route::delete('user/preferences/default-view', [App\Http\Controllers\Api\UserPreferenceController::class, 'clearDefaultView'])->name('api.user.preferences.clear-default-view');

            // Admin Module Management API
            Route::prefix('admin/modules')->name('api.admin.modules.')->group(function () {
                Route::get('/', [App\Http\Controllers\Admin\Api\ModuleManagementController::class, 'index'])->name('index');
                Route::post('/', [App\Http\Controllers\Admin\Api\ModuleManagementController::class, 'store'])->name('store');
                Route::get('/{id}', [App\Http\Controllers\Admin\Api\ModuleManagementController::class, 'show'])->name('show');
                Route::put('/{id}', [App\Http\Controllers\Admin\Api\ModuleManagementController::class, 'update'])->name('update');
                Route::delete('/{id}', [App\Http\Controllers\Admin\Api\ModuleManagementController::class, 'destroy'])->name('destroy');
                Route::patch('/{id}/activate', [App\Http\Controllers\Admin\Api\ModuleManagementController::class, 'activate'])->name('activate');
                Route::patch('/{id}/deactivate', [App\Http\Controllers\Admin\Api\ModuleManagementController::class, 'deactivate'])->name('deactivate');
                Route::post('/{id}/sync-structure', [App\Http\Controllers\Admin\Api\ModuleManagementController::class, 'syncStructure'])->name('sync-structure');

                // Block Management API
                Route::prefix('{moduleId}/blocks')->name('blocks.')->group(function () {
                    Route::get('/', [App\Http\Controllers\Admin\Api\BlockManagementController::class, 'index'])->name('index');
                    Route::post('/', [App\Http\Controllers\Admin\Api\BlockManagementController::class, 'store'])->name('store');
                    Route::put('/{id}', [App\Http\Controllers\Admin\Api\BlockManagementController::class, 'update'])->name('update');
                    Route::delete('/{id}', [App\Http\Controllers\Admin\Api\BlockManagementController::class, 'destroy'])->name('destroy');
                    Route::post('/reorder', [App\Http\Controllers\Admin\Api\BlockManagementController::class, 'reorder'])->name('reorder');

                    // Field Management API
                    Route::prefix('{blockId}/fields')->name('fields.')->group(function () {
                        Route::get('/', [App\Http\Controllers\Admin\Api\FieldManagementController::class, 'index'])->name('index');
                        Route::post('/', [App\Http\Controllers\Admin\Api\FieldManagementController::class, 'store'])->name('store');
                        Route::put('/{id}', [App\Http\Controllers\Admin\Api\FieldManagementController::class, 'update'])->name('update');
                        Route::delete('/{id}', [App\Http\Controllers\Admin\Api\FieldManagementController::class, 'destroy'])->name('destroy');
                        Route::post('/reorder', [App\Http\Controllers\Admin\Api\FieldManagementController::class, 'reorder'])->name('reorder');
                    });
                });
            });
        });

        // Settings routes
        require __DIR__.'/settings.php';
    });
});

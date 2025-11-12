<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

// Dashboard route moved to tenant.php for multi-tenant architecture
// Central app dashboard removed - all users access via tenant subdomains

// Auth routes moved to tenant.php - authentication is tenant-specific
// Settings routes moved to tenant.php - settings are tenant-specific

// Demo routes (remove in production)
// Commented out - old form wrappers demo, replaced by dynamic-form in tenant routes
// Route::get('demo/form-inputs', function () {
//     return Inertia::render('demo/FormInputs');
// })->name('demo.form-inputs');

# Simple Multi-Tenancy for Local Development

## The Problem with the Previous Setup

- Nginx requires www-data permissions
- Constant permission conflicts
- Complex configuration
- Not suitable for local development

## New Approach: Route-Based Multi-Tenancy

Instead of subdomain-based tenancy (acme.localhost, startup.localhost), we'll use **route-based** tenancy which is much simpler for local development:

- `http://localhost:8000/tenant/acme/dashboard`
- `http://localhost:8000/tenant/startup/dashboard`
- `http://localhost:8000/tenant/enterprise/dashboard`

### Benefits

✅ No nginx configuration needed
✅ No permission issues
✅ Works with PHP built-in server
✅ Works with `php artisan serve`
✅ Easy to test multiple tenants
✅ Still isolated databases per tenant

## Setup Instructions

### 1. Use PHP's Built-in Server (No Nginx)

```bash
php artisan serve
```

That's it! Access at http://localhost:8000

### 2. Alternative: Simplified Subdomain with Laravel Valet

If you want subdomains for local dev, use Laravel Valet (much simpler than nginx):

```bash
composer global require laravel/valet
valet install
cd /home/chris/PersonalProjects/VrtxCRM
valet link vrtxcrm
valet secure vrtxcrm  # Optional: HTTPS
```

Then access at:
- http://vrtxcrm.test
- http://acme.vrtxcrm.test
- http://startup.vrtxcrm.test

Valet handles all the nginx configuration automatically with proper permissions.

## Recommended: Route-Based for Simplicity

Let's implement route-based tenancy which is the simplest for local development.

### How It Works

1. URL pattern: `/tenant/{tenant_slug}/...`
2. Middleware extracts tenant from URL
3. Switches database connection
4. All routes work normally after that

### Implementation

```php
// routes/web.php
Route::prefix('tenant/{tenant}')->middleware(['tenant.identify'])->group(function () {
    require __DIR__.'/auth.php';

    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->middleware(['auth'])->name('dashboard');

    // All your other routes...
});
```

### URLs

- Login: `http://localhost:8000/tenant/acme/login`
- Dashboard: `http://localhost:8000/tenant/acme/dashboard`
- Settings: `http://localhost:8000/tenant/startup/settings`

## Which Approach Do You Want?

1. **Route-based** (Recommended for local dev)
   - Simplest setup
   - No configuration needed
   - Works with `php artisan serve`

2. **Laravel Valet** (If you want subdomains)
   - One-time setup
   - Clean subdomain URLs
   - Handles all permissions automatically

3. **Keep nginx** (If you must)
   - Need to fix permissions once
   - Then it works
   - But complex for local dev

Let me know which one you prefer and I'll set it up!

# Reset Steps - Remove Multi-Tenancy

## Current Status

I've removed most of the multi-tenancy setup but hit permission errors. Here's what I've done and what you need to do:

## ✅ Completed

1. Removed stancl/tenancy package
2. Deleted config/tenancy.php
3. Deleted routes/tenant.php
4. Deleted database/migrations/tenant directory
5. Deleted app/Models/Tenancy directory
6. Deleted tenant seeders
7. Dropped all 5 tenant databases (tenantacme-corp, tenantstartup-inc, etc.)
8. Removed manual routes files (resources/js/routes/manual.ts, scripts/ensure-routes.js)

## ⚠️ YOU NEED TO RUN (Requires sudo)

Run these commands in order:

### 1. Fix Permissions

```bash
./fix-permissions.sh
```

OR manually:

```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
sudo usermod -a -G www-data $USER
```

### 2. Remove Bootstrap Cache

```bash
sudo rm -f bootstrap/cache/*.php
```

### 3. Run Composer Autoload

```bash
composer dump-autoload
```

### 4. Clear All Caches

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### 5. Drop and Recreate Main Database

```bash
php artisan migrate:fresh --seed
```

### 6. Create a Test User

```bash
php artisan tinker
```

Then in tinker:

```php
\App\Models\User::create([
    'name' => 'Test User',
    'email' => 'test@example.com',
    'password' => bcrypt('password'),
    'email_verified_at' => now()
]);
exit
```

### 7. Regenerate Routes

```bash
php artisan wayfinder:generate
```

### 8. Restart Vite

Stop Vite (Ctrl+C) then:

```bash
npm run dev
```

### 9. Test Login

Visit: http://localhost:8000/login

Login with:
- Email: test@example.com
- Password: password

## What This Will Do

- Remove all multi-tenancy infrastructure
- Reset to a single-database Laravel application
- Create a fresh database with one test user
- Regenerate TypeScript routes properly
- Get login working again

## Files Removed

- config/tenancy.php
- routes/tenant.php
- database/migrations/tenant/
- app/Models/Tenancy/
- database/seeders/TenantSeeder.php
- database/seeders/TenantDatabaseSeeder.php
- database/seeders/TenantUserSeeder.php
- resources/js/routes/manual.ts
- scripts/ensure-routes.js

## Databases

- Dropped: All 5 tenant databases
- Kept: vrtx (main database) - will be reset with migrate:fresh

## After This

You'll have a clean Laravel + Svelte application with:
- Basic authentication
- Single database
- No multi-tenancy
- Working routes
- One test user

Then we can start fresh if you want to add features.

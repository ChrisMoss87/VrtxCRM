# Emergency Fix Guide

## Current Status

✅ Routes moved to tenant context (demo/dynamic-form now in routes/tenant.php)
✅ Auth routes added to tenant context (login/register now work with tenant databases)
✅ Manual routes system created (resources/js/routes/manual.ts)
✅ Route verification script created (scripts/ensure-routes.js)
✅ Playwright testing framework set up
✅ MCP server configured for browser automation
⚠️ Restart Claude Code to activate MCP server
⚠️ Run `node scripts/ensure-routes.js` if home export error returns

## Issues Fixed

### Issue 1: Permission Denied on Logs

Run these commands:

```bash
# Fix permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Add yourself to www-data group
sudo usermod -a -G www-data $USER

# Apply group changes immediately
newgrp www-data
```

### Issue 2: Route Accessing Wrong Database (FIXED)

✅ The `/demo/dynamic-form` route has been moved to `routes/tenant.php`
✅ Auth routes have been added to `routes/tenant.php`

This ensures:
- Routes in `routes/web.php` use the **central database**
- Routes in `routes/tenant.php` use the **tenant database** (has modules table and users)
- The dynamic form and authentication work with tenant-specific data

### Issue 3: Missing 'home' Export in TypeScript Routes (FIXED)

**Root Cause**: The wayfinder package doesn't auto-generate routes for closure-based routes (only controller-based routes). Auto-formatting and wayfinder regeneration kept removing manual additions.

**Solution**: Created a two-file system:
- `resources/js/routes/manual.ts` - Contains manual route definitions
- `resources/js/routes/index.ts` - Auto-generated file that imports from manual.ts
- `scripts/ensure-routes.js` - Verification script to fix the export if it gets removed

**How to Fix if Error Returns**:
```bash
node scripts/ensure-routes.js
# Then restart Vite dev server
```

## Quick Start

1. **Fix Permissions** (if not already done):
   ```bash
   ./fix-permissions.sh
   ```

2. **Ensure Routes Are Valid**:
   ```bash
   node scripts/ensure-routes.js
   ```

3. **Clear Cache**:
   ```bash
   php artisan route:clear
   php artisan cache:clear
   ```

4. **Start Vite** (in a separate terminal):
   ```bash
   npm run dev
   ```

5. **Visit App**:
   - URL: http://acme.localhost/login
   - Email: admin@test.com
   - Password: password

## Testing

Run automated browser tests:

```bash
# Run all tests
npm run test:browser

# Interactive mode (recommended)
npm run test:browser:ui

# Debug mode
npm run test:browser:debug
```

See TESTING.md for complete testing documentation.

## Quick Commands (Copy & Paste)

```bash
# Fix permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
sudo usermod -a -G www-data $USER
newgrp www-data

# Clear cache
php artisan route:clear
php artisan cache:clear
```

Then move the route from `routes/web.php` to `routes/tenant.php`.

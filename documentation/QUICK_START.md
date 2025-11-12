# Quick Start Guide - VrtxCRM Multi-Tenant Setup

This guide will get your multi-tenant CRM running with Nginx in under 10 minutes.

## Prerequisites

- Ubuntu/Debian Linux
- Nginx installed
- PHP 8.4-FPM installed
- PostgreSQL running via Docker
- Node.js and npm installed

## Quick Setup Commands

### Step 1: Install Nginx Configuration

**Note:** No need to edit /etc/hosts! The `.localhost` domains work automatically.

```bash
# Backup existing config
sudo cp /etc/nginx/sites-available/vrtxCRM /etc/nginx/sites-available/vrtxCRM.backup.$(date +%Y%m%d)

# Install new config
sudo cp nginx-config.conf /etc/nginx/sites-available/vrtxCRM

# Test configuration
sudo nginx -t

# Reload Nginx
sudo systemctl reload nginx
```

### Step 3: Start PHP-FPM

```bash
# Check if running
sudo systemctl status php8.4-fpm

# If not running, start it
sudo systemctl start php8.4-fpm
sudo systemctl enable php8.4-fpm
```

### Step 4: Set Permissions

**Option A - Quick (using the script):**
```bash
./fix-permissions.sh
```

**Option B - Manual:**
```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Add yourself to www-data group
sudo usermod -a -G www-data $USER

# Allow Nginx to access project directory
sudo chmod +x /home /home/chris /home/chris/PersonalProjects
```

**Note:** If you added yourself to www-data group, log out and back in for it to take effect.

### Step 5: Start Vite Dev Server

```bash
npm run dev
```

Keep this running in a separate terminal.

### Step 6: Access Your Application

Open your browser and visit any `.localhost` domain:

**Tenant Domains (work automatically, no /etc/hosts needed!):**
- http://acme.localhost
- http://startup.localhost
- http://enterprise.localhost
- http://suspended.localhost
- http://expired.localhost
- http://any-name-you-want.localhost (any subdomain works!)

### Step 7: Login

Use these credentials for any tenant:

```
Email: admin@test.com
Password: password
```

OR

```
Email: user@test.com
Password: password
```

## Test Multi-Tenancy

1. Visit http://acme.localhost
2. Login with admin@test.com / password
3. Navigate to /demo/dynamic-form
4. Create some test data
5. Logout
6. Visit http://startup.localhost
7. Login with admin@test.com / password
8. Verify you cannot see data from Acme Corp

This confirms tenant isolation is working!

## Available Routes

- `/` - Home page
- `/dashboard` - Dashboard (requires login)
- `/demo/dynamic-form` - Dynamic form demo with database-driven fields
- `/demo/form-inputs` - Original form inputs demo

## Troubleshooting

### 502 Bad Gateway

**Cause:** PHP-FPM is not running

**Solution:**
```bash
sudo systemctl restart php8.4-fpm
```

### 403 Forbidden

**Cause:** Permission issues

**Solution:**
```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
sudo chmod +x /home /home/chris /home/chris/PersonalProjects
```

### Assets Not Loading (CSS/JS)

**Cause:** Vite dev server not running

**Solution:**
```bash
npm run dev
```

Or build production assets:
```bash
npm run build
```

### "Connection Refused" or "Unable to Connect"

**Cause:** Nginx not running

**Solution:**
```bash
sudo systemctl status nginx
sudo systemctl start nginx
```

### Check Logs

**Nginx Error Log:**
```bash
sudo tail -f /var/log/nginx/vrtxcrm-error.log
```

**PHP-FPM Error Log:**
```bash
sudo tail -f /var/log/php8.4-fpm.log
```

**Laravel Log:**
```bash
tail -f storage/logs/laravel.log
```

## Production Deployment

For production, build assets instead of using dev server:

```bash
# Build production assets
npm run build

# Ensure .env is set to production
APP_ENV=production
APP_DEBUG=false
```

## Database Management

**Access Central Database:**
```bash
docker exec -it vrtxcrm-postgres-1 psql -U vrtx -d vrtx
```

**Access Tenant Database:**
```bash
# Example: Acme Corp tenant
docker exec -it vrtxcrm-postgres-1 psql -U vrtx -d tenantacme-corp
```

**Run Migrations:**
```bash
# Central database
php artisan migrate

# Tenant databases
php artisan tenants:migrate
```

**Seed Data:**
```bash
# Central database (creates tenants)
php artisan db:seed

# Tenant databases (creates users, modules)
php artisan tenants:seed
```

## One-Line Complete Setup

If you need to reset everything:

```bash
# Drop all tenant databases, migrate fresh, and seed
docker exec vrtxcrm-postgres-1 psql -U vrtx -d postgres -c 'DROP DATABASE IF EXISTS "tenantacme-corp"'; \
docker exec vrtxcrm-postgres-1 psql -U vrtx -d postgres -c 'DROP DATABASE IF EXISTS "tenantstartup-inc"'; \
docker exec vrtxcrm-postgres-1 psql -U vrtx -d postgres -c 'DROP DATABASE IF EXISTS "tenantenterprise-co"'; \
docker exec vrtxcrm-postgres-1 psql -U vrtx -d postgres -c 'DROP DATABASE IF EXISTS "tenantsuspended-biz"'; \
docker exec vrtxcrm-postgres-1 psql -U vrtx -d postgres -c 'DROP DATABASE IF EXISTS "tenantexpired-trial"'; \
php artisan migrate:fresh --seed
```

## Additional Documentation

- **NGINX_SETUP.md** - Detailed Nginx configuration guide
- **TEST_CREDENTIALS.md** - Complete tenant credentials reference
- **CLAUDE.md** - Development guide and architecture overview

## Common Development Commands

```bash
# Start all services
composer dev

# Start Laravel server only
php artisan serve

# Start Vite only
npm run dev

# Run tests
php artisan test

# Run tenant tests
php artisan test --filter=Tenancy

# Clear caches
php artisan optimize:clear

# View logs in real-time
php artisan pail
```

## Support

If you encounter any issues not covered here, check:
1. NGINX_SETUP.md for detailed Nginx troubleshooting
2. Laravel error logs in storage/logs/
3. Nginx error logs in /var/log/nginx/

---

**Remember:** This is for development/testing only. Delete TEST_CREDENTIALS.md before deploying to production!

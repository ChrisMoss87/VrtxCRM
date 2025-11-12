# MCP & Playwright Setup - Complete Guide

## What We've Set Up

### 1. Playwright Browser Testing Framework

Playwright is now fully configured for automated browser testing of your multi-tenant Laravel application.

**Files Created:**
- `playwright.config.ts` - Playwright configuration
- `tests/browser/auth.spec.ts` - Authentication tests
- `package.json` - Added test scripts

**Commands Available:**
```bash
npm run test:browser       # Run all tests headless
npm run test:browser:ui    # Interactive UI mode
npm run test:browser:debug # Debug mode with inspector
```

### 2. MCP Server for Claude Code

Model Context Protocol (MCP) allows Claude Code to use Playwright for browser automation.

**Configuration File:**
`~/.config/claude-code/mcp_config.json`

```json
{
  "mcpServers": {
    "playwright": {
      "command": "npx",
      "args": ["-y", "@executeautomation/playwright-mcp-server"]
    }
  }
}
```

**Important**: You must **restart Claude Code** for the MCP server to activate.

### 3. Route Management System

Created a robust solution for the persistent "home export not found" error.

**Files Created:**
- `resources/js/routes/manual.ts` - Manual route definitions
- `scripts/ensure-routes.js` - Route verification/fixing script

**How It Works:**
1. Wayfinder auto-generates routes in `routes/index.ts`
2. Manual routes (like `home`) are defined in `routes/manual.ts`
3. `routes/index.ts` imports from `manual.ts`
4. The ensure-routes script verifies this setup

**Usage:**
```bash
# Verify routes are correctly set up
node scripts/ensure-routes.js

# Output if OK:
# ✅ Manual routes export already exists
# ✅ Manual routes file exists
# ✅ Routes configuration is valid

# If routes need fixing, it auto-fixes them
```

## Why This Fixes Your Issues

### Issue: "home export not found"

**Root Cause:**
- Laravel Wayfinder only generates routes for controller-based routes
- Your home route in `routes/web.php` uses a closure: `Route::get('/', function() ...)`
- When Wayfinder regenerates or formatters run, manual additions get removed

**Solution:**
- Separate manual routes into `manual.ts`
- Auto-generated `index.ts` imports from `manual.ts`
- Even if `index.ts` gets regenerated, the import statement is minimal and survives
- Verification script can auto-fix if import gets removed

### Issue: Testing Multi-Tenant Auth

**Root Cause:**
- Manual browser testing is time-consuming
- Hard to verify route errors without browser console
- Difficult to test tenant isolation

**Solution:**
- Playwright tests automate the full browser flow
- Tests catch JavaScript errors in console
- Can test multiple tenants in one test
- MCP server allows Claude to help debug with Playwright

## How to Use

### Daily Development Workflow

1. **Start Development:**
   ```bash
   # Ensure routes are valid
   node scripts/ensure-routes.js

   # Start Vite
   npm run dev
   ```

2. **After Running Wayfinder:**
   ```bash
   # Regenerate routes
   php artisan wayfinder:generate

   # Fix manual routes
   node scripts/ensure-routes.js
   ```

3. **Before Committing:**
   ```bash
   # Run tests
   npm run test:browser
   ```

### Using MCP with Claude Code

After restarting Claude Code, you can ask Claude to:

- "Use Playwright to test the login flow"
- "Check if there are any console errors on the login page"
- "Test multi-tenant isolation between acme and startup tenants"
- "Capture a screenshot of the dynamic form"
- "Verify all form fields render correctly"

Claude will use the Playwright MCP server to automate these tasks.

### Writing New Tests

Add new test files in `tests/browser/`:

```typescript
import { test, expect } from '@playwright/test';

test('my new test', async ({ page }) => {
  await page.goto('/login');
  // ... your test code ...
});
```

## What Each File Does

### `/home/chris/.config/claude-code/mcp_config.json`
Configures Claude Code to use the Playwright MCP server.

### `playwright.config.ts`
Main Playwright configuration:
- Base URL: http://acme.localhost
- Browser: Chromium
- Starts Vite dev server automatically when running tests

### `tests/browser/auth.spec.ts`
Authentication flow tests:
1. Login page loads without JavaScript errors
2. Login works with valid credentials
3. Dynamic form accessible after login

### `scripts/ensure-routes.js`
Verification script that:
- Checks if manual routes export exists in `routes/index.ts`
- Adds it if missing
- Verifies `routes/manual.ts` exists
- Reports success/failure

### `resources/js/routes/manual.ts`
Contains manually defined routes that Wayfinder doesn't auto-generate:
- `home` route (points to `/`)
- Any future manual routes

## Troubleshooting

### MCP Server Not Working

1. Check configuration exists:
   ```bash
   cat ~/.config/claude-code/mcp_config.json
   ```

2. Restart Claude Code (close and reopen)

3. Check if MCP tools are available (Claude will show playwright tools in the tool list)

### Routes Keep Breaking

```bash
# Quick fix
node scripts/ensure-routes.js

# If that doesn't work, check files exist:
ls -la resources/js/routes/manual.ts
ls -la scripts/ensure-routes.js
```

### Playwright Tests Failing

```bash
# Ensure Vite is running
npm run dev

# Check Nginx is running
sudo systemctl status nginx

# Clear caches
php artisan route:clear
php artisan cache:clear

# Fix routes
node scripts/ensure-routes.js
```

### Can't Access Login Page in Tests

1. Check Nginx config points to correct path
2. Ensure auth routes are in `routes/tenant.php`
3. Check users exist in tenant database:
   ```bash
   docker exec vrtxcrm-postgres-1 psql -U vrtx -d tenantacme-corp -c "SELECT * FROM users;"
   ```

## Next Steps

### Recommended Tests to Add

1. **Multi-tenant isolation:**
   ```typescript
   test('data is isolated between tenants', async ({ page }) => {
     // Create data in acme.localhost
     // Login to startup.localhost
     // Verify data is not visible
   });
   ```

2. **Form validation:**
   ```typescript
   test('form shows validation errors', async ({ page }) => {
     // Submit empty form
     // Check error messages appear
   });
   ```

3. **Database-driven fields:**
   ```typescript
   test('form renders all fields from database', async ({ page }) => {
     // Check all fields from TestFormSeeder are rendered
   });
   ```

### Improvements

1. **Add CI/CD:**
   - Run tests on every PR
   - Auto-verify routes on build

2. **Visual regression testing:**
   - Capture screenshots
   - Compare against baseline

3. **Performance testing:**
   - Measure page load times
   - Track JS bundle sizes

## Resources

- [Playwright Docs](https://playwright.dev)
- [MCP Specification](https://modelcontextprotocol.io)
- [Playwright MCP Server](https://github.com/executeautomation/playwright-mcp-server)
- [Laravel Wayfinder](https://github.com/laravel/wayfinder)

## Summary

You now have:
- ✅ Automated browser testing with Playwright
- ✅ MCP server for Claude-assisted browser automation
- ✅ Robust route management that survives formatting/regeneration
- ✅ Verification scripts to ensure everything stays working
- ✅ Documentation for maintaining the system

**Next Action:** Restart Claude Code to activate the MCP server, then ask Claude to run a Playwright test!

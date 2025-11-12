
# Testing Guide - VrtxCRM

## Browser Testing with Playwright

### Setup

Playwright is configured and ready to use. The MCP server for Playwright is also set up in Claude Code.

### Running Tests

```bash
# Run all browser tests
npm run test:browser

# Run tests with UI (interactive mode)
npm run test:browser:ui

# Run tests in debug mode
npm run test:browser:debug
```

### Test Files

- `tests/browser/auth.spec.ts` - Authentication flow tests
  - Login page loads without errors
  - Successful login with valid credentials
  - Dynamic form accessible after login

### Configuration

- **Base URL**: http://acme.localhost
- **Browser**: Chromium (Chrome)
- **Test credentials**: admin@test.com / password

## Route Issues & Fixes

### The Problem

The `home` route export keeps disappearing from `resources/js/routes/index.ts` because:
1. Wayfinder only auto-generates routes for controller-based routes
2. The home route uses a closure in `routes/web.php`
3. Auto-formatting or Wayfinder regeneration removes manual additions

### The Solution

We've implemented a two-file approach:

1. **`resources/js/routes/manual.ts`** - Contains manually defined routes (like `home`)
2. **`resources/js/routes/index.ts`** - Auto-generated, but imports from `manual.ts`

### Ensuring Routes Stay Fixed

Run this script to verify/fix the routes configuration:

```bash
node scripts/ensure-routes.js
```

This script:
- Checks if the manual routes export exists in `index.ts`
- Adds it if missing
- Verifies `manual.ts` exists
- Reports the status

### When to Run

- Before running tests
- After running `php artisan wayfinder:generate`
- If you see the "home export not found" error again

## MCP Server (Model Context Protocol)

### What is MCP?

MCP allows Claude Code to use external tools like Playwright for browser automation, testing, and debugging.

### Configuration

MCP is configured in `~/.config/claude-code/mcp_config.json`:

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

### Using MCP with Playwright

With the Playwright MCP server, Claude Code can:
- Automate browser interactions
- Debug routing issues
- Test multi-tenant functionality
- Capture screenshots and videos
- Inspect network requests
- Verify database-driven forms

### Restart Required

After setting up the MCP server, **restart Claude Code** for changes to take effect.

## Common Testing Scenarios

### Test Multi-Tenant Isolation

```typescript
test('tenants have isolated data', async ({ page }) => {
  // Login to Acme Corp tenant
  await page.goto('http://acme.localhost/login');
  await page.fill('[name="email"]', 'admin@test.com');
  await page.fill('[name="password"]', 'password');
  await page.click('button[type="submit"]');

  // Create data in Acme Corp
  await page.goto('/demo/dynamic-form');
  // ... fill form ...

  // Switch to Startup Inc tenant
  await page.goto('http://startup.localhost/login');
  await page.fill('[name="email"]', 'admin@test.com');
  await page.fill('[name="password"]', 'password');
  await page.click('button[type="submit"]');

  // Verify Acme Corp data is not visible
  await page.goto('/demo/dynamic-form');
  // ... assert data isolation ...
});
```

### Test Database-Driven Forms

```typescript
test('form renders fields from database', async ({ page }) => {
  await page.goto('/login');
  await page.fill('[name="email"]', 'admin@test.com');
  await page.fill('[name="password"]', 'password');
  await page.click('button[type="submit"]');

  await page.goto('/demo/dynamic-form');

  // Verify fields from TestFormSeeder
  await expect(page.getByLabel('First Name')).toBeVisible();
  await expect(page.getByLabel('Last Name')).toBeVisible();
  await expect(page.getByLabel('Email')).toBeVisible();
  await expect(page.getByLabel('Status')).toBeVisible();
  await expect(page.getByLabel('Bio')).toBeVisible();
});
```

## Troubleshooting

### "home export not found" Error

```bash
# Fix it
node scripts/ensure-routes.js

# Then restart Vite
# Press Ctrl+C in the terminal running npm run dev
npm run dev
```

### Tests Failing Due to Route Issues

```bash
# Regenerate routes
php artisan wayfinder:generate

# Ensure manual routes are in place
node scripts/ensure-routes.js

# Clear cache
php artisan route:clear
php artisan cache:clear

# Restart Vite
npm run dev
```

### Playwright Browser Not Installed

```bash
npx playwright install chromium
```

## CI/CD Integration

For GitHub Actions or other CI systems, add to your workflow:

```yaml
- name: Install Playwright Browsers
  run: npx playwright install chromium

- name: Ensure Routes Configuration
  run: node scripts/ensure-routes.js

- name: Run Browser Tests
  run: npm run test:browser
```

## Next Steps

1. Add more test coverage for:
   - Form validation
   - Multi-step workflows
   - Error handling
   - Permissions and authorization

2. Add visual regression testing
3. Add performance testing
4. Add accessibility testing

## Resources

- [Playwright Documentation](https://playwright.dev)
- [MCP Documentation](https://modelcontextprotocol.io)
- [Laravel Wayfinder](https://github.com/laravel/wayfinder)

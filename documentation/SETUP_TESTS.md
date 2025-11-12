# Test Setup Instructions

## Prerequisites

### 1. Add Domain to /etc/hosts

Run this command to add the test domain to your hosts file:

```bash
echo "127.0.0.1 acme.vrtxcrm.local" | sudo tee -a /etc/hosts
```

Verify it was added:
```bash
grep "acme.vrtxcrm.local" /etc/hosts
```

### 2. Ensure Tenant Database Exists

The test tenant should already be created. Verify with:

```bash
php artisan tinker --execute="
use App\Models\Tenancy\Tenant;
\$tenant = Tenant::with('domains')->first();
if (\$tenant) {
    echo \"✓ Tenant exists: {\$tenant->name} ({\$tenant->id})\n\";
    echo \"  Domain: \" . \$tenant->domains->first()?->domain . \"\n\";
} else {
    echo \"✗ No tenant found. Run tenant creation first.\n\";
}
"
```

If no tenant exists, create one:

```bash
php artisan tinker --execute="
\$tenant = app(App\Services\TenantService::class)->createTenant([
    'name' => 'Acme Corporation',
    'subdomain' => 'acme',
    'plan' => 'professional',
    'email' => 'admin@test.com',
]);
echo \"✓ Tenant created: {\$tenant->id}\n\";
"
```

### 3. Start Development Server

Make sure the development server is running:

```bash
npm run dev
```

Or use the full development environment:

```bash
composer dev
```

This will start:
- Laravel dev server (port 8000)
- Vite dev server (port 5173)
- Queue worker
- Pail (log viewer)

---

## Running Tests

### Run All Login Tests

```bash
npm run test:browser
```

### Run Specific Test File

```bash
npx playwright test tests/browser/login-test.spec.ts
```

### Run Comprehensive Login Tests

```bash
npx playwright test tests/browser/login.comprehensive.spec.ts
```

### Run Tests in UI Mode (Interactive)

```bash
npm run test:browser:ui
```

### Run Tests in Debug Mode

```bash
npm run test:browser:debug
```

### Run Only Failed Tests

```bash
npx playwright test --last-failed
```

---

## Test Files

### `/tests/browser/login-test.spec.ts`
Basic login tests:
- Admin user login
- Invalid credentials rejection
- Login page loading

### `/tests/browser/login.comprehensive.spec.ts`
Comprehensive login test suite covering:
- **UI Tests**: Form elements, accessibility
- **Validation Tests**: Required fields, email format
- **Authentication Tests**: Valid/invalid credentials, session persistence
- **Post-Login Tests**: Dashboard redirect, protected routes
- **Error Handling**: Network errors, slow responses
- **Security Tests**: Password visibility, form data clearing

### `/tests/browser/auth.spec.ts`
Authentication flow tests:
- Login page loads without errors
- Successful login with valid credentials
- Access to protected routes after login

### `/tests/browser/fixtures/auth.fixture.ts`
Reusable test fixtures:
- `testUsers` - Test user credentials
- `authenticatedPage` - Pre-authenticated page fixture
- `adminPage` - Pre-authenticated admin page fixture

---

## Test Configuration

### `playwright.config.ts`

Key settings:
- **Base URL**: `http://acme.vrtxcrm.local`
- **Test Directory**: `./tests/browser`
- **Parallel Execution**: Enabled
- **Screenshots**: On failure only
- **Videos**: On failure only
- **Traces**: On first retry

### Browser Settings

Currently configured for:
- Chromium (Desktop Chrome)

To add more browsers, edit `playwright.config.ts`:

```typescript
projects: [
  { name: 'chromium', use: { ...devices['Desktop Chrome'] } },
  { name: 'firefox', use: { ...devices['Desktop Firefox'] } },
  { name: 'webkit', use: { ...devices['Desktop Safari'] } },
],
```

---

## Viewing Test Results

### HTML Report

After running tests, view the HTML report:

```bash
npx playwright show-report
```

### Screenshots and Videos

Failed test artifacts are saved to:
- **Screenshots**: `test-results/*/test-failed-*.png`
- **Videos**: `test-results/*/video.webm`
- **Traces**: `test-results/*/trace.zip`

### View Trace Files

```bash
npx playwright show-trace test-results/*/trace.zip
```

---

## Test Credentials

All credentials are documented in `TEST_CREDENTIALS.md`.

**Default Admin User:**
- Email: `admin@test.com`
- Password: `password`

---

## Troubleshooting

### Tests Fail with "net::ERR_NAME_NOT_RESOLVED"

**Solution**: Add domain to /etc/hosts (see step 1 above)

### Tests Fail with "timeout exceeded"

**Solution**: Ensure development server is running on correct port

Check Vite is running:
```bash
curl http://localhost:5173
```

Check Laravel is running:
```bash
curl http://localhost:8000
```

### Tests Fail with "Database not found"

**Solution**: Ensure tenant database exists

```bash
docker exec vrtxcrm-postgres-1 psql -U vrtx -d vrtx -c "SELECT datname FROM pg_database WHERE datname LIKE 'tenant%';"
```

### Browser Not Opening

Install Playwright browsers:

```bash
npx playwright install
```

Install system dependencies:

```bash
npx playwright install-deps
```

---

## CI/CD Integration

For CI environments, set environment variable:

```bash
export CI=true
```

This enables:
- 2 retries on failure
- Single worker (no parallelization)
- forbidOnly (prevents .only() in tests)

---

## Writing New Tests

### Use Test Fixtures

```typescript
import { test, expect } from './fixtures/auth.fixture';

test('my authenticated test', async ({ authenticatedPage }) => {
  // Already logged in!
  await authenticatedPage.goto('/dashboard');
  // ... test code
});
```

### Use Test Users

```typescript
import { testUsers } from './fixtures/auth.fixture';

await page.fill('#email', testUsers.admin.email);
await page.fill('#password', testUsers.admin.password);
```

### Best Practices

1. **Use semantic selectors**: Prefer `getByRole`, `getByLabel`, `getByText` over CSS selectors
2. **Wait for network idle**: Use `waitForLoadState('networkidle')` after navigation
3. **Take screenshots on failure**: Already configured in playwright.config.ts
4. **Use descriptive test names**: Clear intent helps debugging
5. **Group related tests**: Use `test.describe()` blocks
6. **Isolate tests**: Each test should be independent

---

## Next Steps

1. Run basic login tests to verify setup
2. Review test results and fix any failures
3. Add more test coverage for CRM modules
4. Set up CI/CD pipeline for automated testing

---

**For more information, see:**
- [Playwright Documentation](https://playwright.dev)
- [Playwright Best Practices](https://playwright.dev/docs/best-practices)

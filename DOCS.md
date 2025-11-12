# Documentation

All project documentation has been organized in the **[documentation/](./documentation/)** folder.

## Quick Links

📚 **[View All Documentation](./documentation/README.md)**

### Most Important Docs

- **[Quick Start Guide](./documentation/QUICK_START.md)** - Get started developing
- **[Session Summary](./documentation/SESSION_SUMMARY.md)** - Latest work completed
- **[Test Credentials](./documentation/TEST_CREDENTIALS.md)** - Login credentials

### Recent Fixes

- **[Multi-Tenant Auth Fixed](./documentation/MULTI_TENANT_AUTH_FIXED.md)** - Authentication working ✅
- **[Nginx Setup](./documentation/NGINX_SETUP.md)** - Multi-tenant nginx config
- **[Playwright Tests](./documentation/PLAYWRIGHT_TESTS_READY.md)** - E2E testing ready

### Development

```bash
# Start development
composer dev        # Starts all services

# Or individually:
npm run dev        # Vite dev server
php artisan serve  # Laravel server

# Testing
npx playwright test tests/browser/login-basic.spec.ts
```

### Access

- **Tenant (Acme)**: http://acme.vrtxcrm.local
  - Login: `admin@test.com` / `password`

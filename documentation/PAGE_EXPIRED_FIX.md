# Page Expired Error - Fixed ✓

## Problem

Users getting "Page Expired" (HTTP 419) error when trying to log in at `http://acme.vrtxcrm.local/login`.

## Root Causes

### 1. Missing CSRF Token Meta Tag

The main issue was that the CSRF token meta tag was missing from the HTML head in `resources/views/app.blade.php`.

**Impact**: Inertia.js and Axios couldn't find the CSRF token to include in form submissions, causing Laravel to reject requests with a 419 error.

### 2. CSRF Token Not Configured in Axios

The `resources/js/bootstrap.ts` file wasn't configured to read the CSRF token from the meta tag and add it to Axios request headers.

### 3. Sessions Stored in Tenant Database (Minor Issue)

Sessions were being stored in the tenant database instead of the central database. For multi-tenancy, it's better to store sessions centrally.

## Solutions Applied

### Fix 1: Added CSRF Token Meta Tag

**File**: `resources/views/app.blade.php`

```blade
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">  <!-- ADDED -->

    <title inertia>{{ config('app.name', 'Laravel') }}</title>
```

### Fix 2: Configured Axios to Use CSRF Token

**File**: `resources/js/bootstrap.ts`

```typescript
import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// CSRF token configuration for Axios
const token = document.head.querySelector('meta[name="csrf-token"]');

if (token instanceof HTMLMetaElement) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}
```

### Fix 3: Configured Sessions for Central Database

**File**: `.env`

```env
SESSION_CONNECTION=central
```

This ensures all tenant sessions are stored in the central database, which is the recommended approach for multi-tenancy.

**Cleared cache**:
```bash
php artisan config:clear
php artisan cache:clear
```

## How CSRF Protection Works with Inertia.js

1. **Server Side**: Laravel generates a CSRF token for each session and includes it in the page via `{{ csrf_token() }}`

2. **HTML Head**: The token is placed in a meta tag:
   ```html
   <meta name="csrf-token" content="...token...">
   ```

3. **JavaScript**: Axios reads the token from the meta tag and automatically adds it to all requests:
   ```javascript
   window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
   ```

4. **Form Submission**: When Inertia.js submits a form, Axios includes the `X-CSRF-TOKEN` header

5. **Server Validation**: Laravel's `VerifyCsrfToken` middleware validates the token and accepts the request

## Test Results

### Before Fix
- ❌ Login submitted → 419 Page Expired error
- ❌ No CSRF token in requests
- ❌ Sessions created but login failed

### After Fix
- ✅ Login successful → redirects to dashboard
- ✅ CSRF token included in all requests
- ✅ Sessions working properly
- ✅ All 3 basic Playwright tests passing

```bash
$ npx playwright test tests/browser/login-basic.spec.ts

Running 3 tests using 3 workers

  ✓ Login - Basic Tests › should display login page (969ms)
  ✓ Login - Basic Tests › should show error with invalid credentials (1.0s)
  ✓ Login - Basic Tests › should login successfully with valid credentials (1.3s)

  3 passed (1.7s)
```

## Verification

You can verify the fix by:

1. **Check the page source** - View source of http://acme.vrtxcrm.local/login and look for:
   ```html
   <meta name="csrf-token" content="...">
   ```

2. **Check browser console** - Should NOT see "CSRF token not found" error

3. **Check network tab** - Login POST request should include header:
   ```
   X-CSRF-TOKEN: ...token...
   ```

4. **Test login** - Log in at http://acme.vrtxcrm.local with:
   - Email: admin@test.com
   - Password: password
   - Should redirect to dashboard successfully

## Files Modified

1. `resources/views/app.blade.php` - Added CSRF meta tag
2. `resources/js/bootstrap.ts` - Added Axios CSRF configuration
3. `.env` - Set `SESSION_CONNECTION=central`

## Related Documentation

- [Laravel CSRF Protection](https://laravel.com/docs/csrf)
- [Inertia.js CSRF Protection](https://inertiajs.com/csrf-protection)
- [Multi-Tenant Authentication Fix](./MULTI_TENANT_AUTH_FIXED.md)

## Prevention

To prevent this issue in future projects:

1. Always include CSRF meta tag in base layout templates
2. Configure Axios/HTTP client to read and use CSRF token
3. Test form submissions early in development
4. Use Playwright/E2E tests to catch authentication issues

## Status

✅ **RESOLVED** - Login working correctly with CSRF protection enabled.

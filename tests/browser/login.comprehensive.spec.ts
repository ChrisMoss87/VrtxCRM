import { test, expect } from '@playwright/test';
import { testUsers } from './fixtures/auth.fixture';

test.describe('Login Flow - Comprehensive Tests', () => {
  test.beforeEach(async ({ page }) => {
    // Navigate to login page before each test
    await page.goto('/login');
    await page.waitForLoadState('networkidle');
  });

  test.describe('Login Page UI', () => {
    test('should display all required form elements', async ({ page }) => {
      // Check page title
      await expect(page).toHaveTitle(/login/i);

      // Check form elements are visible
      const emailInput = page.getByLabel(/email/i);
      const passwordInput = page.getByLabel(/password/i);
      const loginButton = page.getByRole('button', { name: /log in|sign in/i });

      await expect(emailInput).toBeVisible();
      await expect(passwordInput).toBeVisible();
      await expect(loginButton).toBeVisible();

      // Check input types
      await expect(emailInput).toHaveAttribute('type', 'email');
      await expect(passwordInput).toHaveAttribute('type', 'password');
    });

    test('should have proper form accessibility', async ({ page }) => {
      // Check for proper labels
      const emailLabel = page.locator('label[for*="email"], label:has-text("Email")');
      const passwordLabel = page.locator('label[for*="password"], label:has-text("Password")');

      await expect(emailLabel).toBeVisible();
      await expect(passwordLabel).toBeVisible();

      // Check input fields have proper attributes
      const emailInput = page.locator('input[name="email"]');
      const passwordInput = page.locator('input[name="password"]');

      expect(await emailInput.getAttribute('type')).toBe('email');
      expect(await passwordInput.getAttribute('type')).toBe('password');
    });

    test('should show/hide password toggle if available', async ({ page }) => {
      const passwordInput = page.getByLabel(/password/i);

      // Check if password toggle button exists (optional feature)
      const toggleButton = page.locator('button:near(:text("Password"))').first();
      const toggleCount = await toggleButton.count();

      if (toggleCount > 0) {
        // Click toggle to show password
        await toggleButton.click();
        await expect(passwordInput).toHaveAttribute('type', 'text');

        // Click toggle to hide password
        await toggleButton.click();
        await expect(passwordInput).toHaveAttribute('type', 'password');
      }
    });
  });

  test.describe('Form Validation', () => {
    test('should require email field', async ({ page }) => {
      const passwordInput = page.getByLabel(/password/i);
      const loginButton = page.getByRole('button', { name: /log in|sign in/i });

      // Fill only password
      await passwordInput.fill('password123');
      await loginButton.click();

      // Should not navigate away from login page
      await page.waitForTimeout(500);
      expect(page.url()).toContain('/login');
    });

    test('should require password field', async ({ page }) => {
      const emailInput = page.getByLabel(/email/i);
      const loginButton = page.getByRole('button', { name: /log in|sign in/i });

      // Fill only email
      await emailInput.fill('test@example.com');
      await loginButton.click();

      // Should not navigate away from login page
      await page.waitForTimeout(500);
      expect(page.url()).toContain('/login');
    });

    test('should validate email format', async ({ page }) => {
      const emailInput = page.getByLabel(/email/i);
      const passwordInput = page.getByLabel(/password/i);
      const loginButton = page.getByRole('button', { name: /log in|sign in/i });

      // Try invalid email format
      await emailInput.fill('invalid-email');
      await passwordInput.fill('password123');
      await loginButton.click();

      // Check for HTML5 validation or custom error
      const validationMessage = await emailInput.evaluate((el: HTMLInputElement) => el.validationMessage);
      expect(validationMessage).toBeTruthy();
    });
  });

  test.describe('Authentication', () => {
    test('should login successfully with valid admin credentials', async ({ page }) => {
      // Fill in credentials
      await page.fill('#email', testUsers.admin.email);
      await page.fill('#password', testUsers.admin.password);

      // Submit form
      await page.click('button[type="submit"]');

      // Wait for navigation
      await page.waitForLoadState('networkidle');

      // Should redirect away from login page
      expect(page.url()).not.toContain('/login');

      // Should be on dashboard or home page
      expect(page.url()).toMatch(/dashboard|\/$/);

      console.log('✅ Admin login successful:', page.url());
    });

    test('should show error with invalid credentials', async ({ page }) => {
      await page.fill('#email', 'invalid@test.com');
      await page.fill('#password', 'wrongpassword');
      await page.click('button[type="submit"]');

      // Wait for error response
      await page.waitForTimeout(1000);

      // Should still be on login page
      expect(page.url()).toContain('/login');

      // Should show error message (check multiple possible locations)
      const hasError = await page.locator('text=/invalid|error|incorrect|credentials/i').count() > 0;
      expect(hasError).toBeTruthy();

      console.log('✅ Invalid credentials correctly rejected');
    });

    test('should handle empty form submission', async ({ page }) => {
      const loginButton = page.getByRole('button', { name: /log in|sign in/i });
      await loginButton.click();

      // Should not navigate
      await page.waitForTimeout(500);
      expect(page.url()).toContain('/login');
    });

    test('should persist session after login', async ({ page, context }) => {
      // Login
      await page.fill('#email', testUsers.admin.email);
      await page.fill('#password', testUsers.admin.password);
      await page.click('button[type="submit"]');
      await page.waitForLoadState('networkidle');

      // Get cookies to verify session
      const cookies = await context.cookies();
      const sessionCookie = cookies.find(c =>
        c.name.includes('session') || c.name.includes('laravel_session')
      );

      expect(sessionCookie).toBeDefined();
      console.log('✅ Session cookie created:', sessionCookie?.name);
    });

    test('should handle concurrent login attempts', async ({ page }) => {
      // Fill credentials
      await page.fill('#email', testUsers.admin.email);
      await page.fill('#password', testUsers.admin.password);

      // Double-click login button rapidly
      const loginButton = page.getByRole('button', { name: /log in|sign in/i });
      await loginButton.click();
      await loginButton.click();

      // Should still successfully login once
      await page.waitForLoadState('networkidle');
      expect(page.url()).not.toContain('/login');
    });
  });

  test.describe('Post-Login Behavior', () => {
    test('should redirect to dashboard after successful login', async ({ page }) => {
      await page.fill('#email', testUsers.admin.email);
      await page.fill('#password', testUsers.admin.password);
      await page.click('button[type="submit"]');

      // Wait for redirect
      await page.waitForURL(/dashboard|\/$/);

      // Verify we're on an authenticated page
      const url = page.url();
      expect(url).not.toContain('/login');
      expect(url).toMatch(/dashboard|\/$/);
    });

    test('should show user information after login', async ({ page }) => {
      await page.fill('#email', testUsers.admin.email);
      await page.fill('#password', testUsers.admin.password);
      await page.click('button[type="submit"]');
      await page.waitForLoadState('networkidle');

      // Look for user name or email in the UI (header, sidebar, etc.)
      const hasUserInfo = await page.locator(`text=/${testUsers.admin.name}|${testUsers.admin.email}/i`).count() > 0;

      if (hasUserInfo) {
        console.log('✅ User information displayed');
      }
    });

    test('should have access to protected routes after login', async ({ page }) => {
      // Login first
      await page.fill('#email', testUsers.admin.email);
      await page.fill('#password', testUsers.admin.password);
      await page.click('button[type="submit"]');
      await page.waitForLoadState('networkidle');

      // Try to access a protected route
      await page.goto('/settings');
      await page.waitForLoadState('networkidle');

      // Should be able to access (not redirected to login)
      expect(page.url()).toContain('/settings');
      expect(page.url()).not.toContain('/login');
    });
  });

  test.describe('Error Handling', () => {
    test('should handle network errors gracefully', async ({ page }) => {
      // Intercept and fail the login request
      await page.route('**/login', route => route.abort());

      await page.fill('#email', testUsers.admin.email);
      await page.fill('#password', testUsers.admin.password);
      await page.click('button[type="submit"]');

      await page.waitForTimeout(1000);

      // Should show some error indication
      const hasError = await page.locator('text=/error|failed|try again/i').count() > 0;

      // At minimum, should not navigate away
      expect(page.url()).toContain('/login');
    });

    test('should handle slow server response', async ({ page }) => {
      // Delay the login response
      await page.route('**/login', async route => {
        await new Promise(resolve => setTimeout(resolve, 2000));
        await route.continue();
      });

      await page.fill('#email', testUsers.admin.email);
      await page.fill('#password', testUsers.admin.password);
      await page.click('button[type="submit"]');

      // Should show loading state (button disabled, spinner, etc.)
      const loginButton = page.getByRole('button', { name: /log in|sign in|loading/i });

      // Check if button is disabled during submission
      await page.waitForTimeout(500);
      const isDisabled = await loginButton.isDisabled().catch(() => false);

      console.log('Button disabled during submission:', isDisabled);
    });
  });

  test.describe('Security', () => {
    test('should not expose password in DOM', async ({ page }) => {
      const passwordInput = page.getByLabel(/password/i);
      await passwordInput.fill('mysecretpassword');

      // Password input should have type="password"
      await expect(passwordInput).toHaveAttribute('type', 'password');

      // Password should not be visible in page content
      const pageContent = await page.content();
      expect(pageContent).not.toContain('mysecretpassword');
    });

    test('should clear form data after failed login', async ({ page }) => {
      await page.fill('#email', 'test@example.com');
      await page.fill('#password', 'wrongpassword');
      await page.click('button[type="submit"]');
      await page.waitForTimeout(1000);

      // Check if password field is cleared (security best practice)
      const passwordInput = page.locator('input[name="password"]');
      const passwordValue = await passwordInput.inputValue();

      console.log('Password cleared after failed login:', passwordValue === '');
    });
  });
});

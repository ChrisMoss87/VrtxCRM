import { test, expect } from '@playwright/test';
import { testUsers } from './fixtures/auth.fixture';

test.describe('User Login Test', () => {
  test('login as admin user', async ({ page }) => {
    // Go to login page
    await page.goto('/login');

    // Wait for page to load
    await page.waitForLoadState('networkidle');

    // Fill in login form using ID selectors (more reliable)
    await page.fill('#email', testUsers.admin.email);
    await page.fill('#password', testUsers.admin.password);

    // Wait a moment for any client-side validation
    await page.waitForTimeout(300);

    // Take screenshot before clicking
    await page.screenshot({ path: 'test-results/before-login.png' });

    // Click login button and wait for navigation
    await Promise.all([
      page.waitForURL('**/*', { waitUntil: 'networkidle', timeout: 10000 }).catch(() => {}),
      page.click('button[type="submit"]')
    ]);

    // Take screenshot after
    await page.screenshot({ path: 'test-results/after-login.png' });

    // Check current URL
    const currentUrl = page.url();
    console.log('Current URL after login:', currentUrl);

    // Verify we're logged in (not on login page anymore)
    expect(currentUrl).not.toContain('/login');

    console.log('✅ Login successful!');
  });

  test('should reject invalid credentials', async ({ page }) => {
    await page.goto('/login');
    await page.waitForLoadState('networkidle');

    // Try to login with invalid credentials
    await page.fill('input[name="email"]', 'invalid@test.com');
    await page.fill('input[name="password"]', 'wrongpassword');
    await page.click('button[type="submit"]');

    // Wait for response
    await page.waitForTimeout(1000);

    // Should still be on login page or show error
    const currentUrl = page.url();
    const hasError = await page.locator('text=/invalid|error|incorrect/i').count() > 0;

    expect(currentUrl.includes('/login') || hasError).toBeTruthy();
    console.log('✅ Invalid credentials correctly rejected');
  });

  test('verify login page loads', async ({ page }) => {
    await page.goto('/login');

    // Wait for page to load
    await page.waitForLoadState('networkidle');

    // Check for email and password inputs
    const emailInput = page.locator('input[name="email"]');
    const passwordInput = page.locator('input[name="password"]');

    await expect(emailInput).toBeVisible();
    await expect(passwordInput).toBeVisible();

    console.log('✅ Login page loaded successfully');
  });
});

import { test, expect } from '@playwright/test';

test.describe('Login - Basic Tests', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/login');
    // Wait for Inertia to be ready
    await page.waitForLoadState('networkidle');
  });

  test('should display login page', async ({ page }) => {
    await expect(page).toHaveTitle(/Login/);
    await expect(page.locator('#email')).toBeVisible();
    await expect(page.locator('#password')).toBeVisible();
    await expect(page.locator('button[type="submit"]')).toBeVisible();
  });

  test('should login successfully with valid credentials', async ({ page }) => {
    // Fill in the form
    await page.fill('#email', 'admin@test.com');
    await page.fill('#password', 'password');

    // Click submit and wait for navigation
    await Promise.all([
      page.waitForURL((url) => !url.pathname.includes('/login'), { timeout: 10000 }),
      page.click('button[type="submit"]'),
    ]);

    // Should be redirected away from login page
    expect(page.url()).not.toContain('/login');

    console.log('Logged in, current URL:', page.url());
  });

  test('should show error with invalid credentials', async ({ page }) => {
    await page.fill('#email', 'admin@test.com');
    await page.fill('#password', 'wrongpassword');

    await page.click('button[type="submit"]');
    await page.waitForLoadState('networkidle');

    // Should still be on login page
    expect(page.url()).toContain('/login');
  });
});

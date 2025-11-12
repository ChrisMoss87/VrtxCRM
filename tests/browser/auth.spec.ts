import { test, expect } from '@playwright/test';

test.describe('Authentication', () => {
  test('should load login page without errors', async ({ page }) => {
    // Navigate to login page
    await page.goto('/login');

    // Check for JavaScript errors in console
    page.on('console', (msg) => {
      if (msg.type() === 'error') {
        console.error('Browser console error:', msg.text());
      }
    });

    // Check for uncaught exceptions
    page.on('pageerror', (error) => {
      console.error('Uncaught exception:', error);
      throw error;
    });

    // Wait for page to be loaded
    await page.waitForLoadState('networkidle');

    // Check that we're on the login page
    await expect(page).toHaveTitle(/Login/i);

    // Check for email and password fields
    await expect(page.getByLabel(/email/i)).toBeVisible();
    await expect(page.getByLabel(/password/i)).toBeVisible();

    // Check for login button
    await expect(page.getByRole('button', { name: /log in|sign in/i })).toBeVisible();
  });

  test('should login successfully with valid credentials', async ({ page }) => {
    await page.goto('/login');

    // Fill in login form
    await page.getByLabel(/email/i).fill('admin@test.com');
    await page.getByLabel(/password/i).fill('password');

    // Click login button
    await page.getByRole('button', { name: /log in|sign in/i }).click();

    // Wait for navigation after login
    await page.waitForURL(/dashboard|\/$/);

    // Verify we're logged in (check for user-specific content)
    await expect(page).not.toHaveURL(/login/);
  });

  test('should access dynamic form after login', async ({ page }) => {
    // Login first
    await page.goto('/login');
    await page.getByLabel(/email/i).fill('admin@test.com');
    await page.getByLabel(/password/i).fill('password');
    await page.getByRole('button', { name: /log in|sign in/i }).click();
    await page.waitForURL(/dashboard|\/$/);

    // Navigate to dynamic form
    await page.goto('/demo/dynamic-form');

    // Wait for form to load
    await page.waitForLoadState('networkidle');

    // Check that form fields are rendered from database
    await expect(page.getByLabel(/first name/i)).toBeVisible();
    await expect(page.getByLabel(/last name/i)).toBeVisible();
    await expect(page.getByLabel(/email/i)).toBeVisible();
  });
});

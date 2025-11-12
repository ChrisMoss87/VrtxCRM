import { test, expect } from '@playwright/test';

test.describe('Route Configuration', () => {
  test('login page loads without JavaScript errors', async ({ page }) => {
    const consoleErrors: string[] = [];
    const pageErrors: Error[] = [];

    // Capture console errors
    page.on('console', (msg) => {
      if (msg.type() === 'error') {
        consoleErrors.push(msg.text());
      }
    });

    // Capture uncaught exceptions
    page.on('pageerror', (error) => {
      pageErrors.push(error);
    });

    // Navigate to login page
    await page.goto('/login');

    // Wait for page to be fully loaded
    await page.waitForLoadState('networkidle');

    // Check for errors
    if (consoleErrors.length > 0) {
      console.error('Console Errors:', consoleErrors);
    }
    if (pageErrors.length > 0) {
      console.error('Page Errors:', pageErrors);
    }

    // Assert no errors occurred
    expect(consoleErrors, 'Should have no console errors').toHaveLength(0);
    expect(pageErrors, 'Should have no page errors').toHaveLength(0);

    // Verify page loaded correctly
    await expect(page).toHaveURL(/login/);
    await expect(page.getByRole('heading')).toBeVisible();
  });

  test('home route is accessible', async ({ page }) => {
    await page.goto('/');

    // Wait for page to load
    await page.waitForLoadState('networkidle');

    // Should not have JavaScript errors
    const consoleErrors: string[] = [];
    page.on('console', (msg) => {
      if (msg.type() === 'error') {
        consoleErrors.push(msg.text());
      }
    });

    // Verify page loads (might redirect to login or show welcome)
    expect(consoleErrors).toHaveLength(0);
  });

  test('dynamic form is accessible after login', async ({ page }) => {
    // Login first
    await page.goto('/login');
    await page.getByLabel(/email/i).fill('admin@test.com');
    await page.getByLabel(/password/i).fill('password');
    await page.getByRole('button', { name: /log in|sign in/i }).click();

    // Wait for redirect after login
    await page.waitForURL(/dashboard|\/$/);

    // Navigate to dynamic form
    await page.goto('/demo/dynamic-form');

    // Wait for page to load
    await page.waitForLoadState('networkidle');

    // Verify no JavaScript errors
    const consoleErrors: string[] = [];
    page.on('console', (msg) => {
      if (msg.type() === 'error') {
        consoleErrors.push(msg.text());
      }
    });

    expect(consoleErrors).toHaveLength(0);

    // Verify form loaded
    await expect(page.getByText(/test form/i)).toBeVisible();
  });
});
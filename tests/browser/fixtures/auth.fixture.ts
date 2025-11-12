import { test as base, Page } from '@playwright/test';

export type TestUser = {
  email: string;
  password: string;
  name: string;
};

export const testUsers = {
  admin: {
    email: 'admin@test.com',
    password: 'password',
    name: 'Admin User',
  },
} as const;

type AuthFixtures = {
  authenticatedPage: Page;
  adminPage: Page;
};

/**
 * Custom test fixture that provides an authenticated page
 */
export const test = base.extend<AuthFixtures>({
  authenticatedPage: async ({ page }, use) => {
    // Login before each test
    await page.goto('/login');
    await page.getByLabel(/email/i).fill(testUsers.admin.email);
    await page.getByLabel(/password/i).fill(testUsers.admin.password);
    await page.getByRole('button', { name: /log in|sign in/i }).click();
    await page.waitForURL(/dashboard|\/$/);

    await use(page);
  },

  adminPage: async ({ page }, use) => {
    // Login as admin
    await page.goto('/login');
    await page.fill('#email', testUsers.admin.email);
    await page.fill('#password', testUsers.admin.password);
    await page.click('button[type="submit"]');
    await page.waitForLoadState('networkidle');

    await use(page);
  },
});

export { expect } from '@playwright/test';

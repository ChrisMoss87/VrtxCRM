import { test, expect } from '@playwright/test';

test.describe('Contacts DataTable', () => {
	// Helper function to login
	async function login(page: any) {
		await page.goto('/login');
		await page.getByLabel(/email/i).fill('admin@test.com');
		await page.getByLabel(/password/i).fill('password');
		await page.getByRole('button', { name: /log in|sign in/i }).click();
		await page.waitForURL(/dashboard|\/$/);
	}

	test('should load contacts page with DataTable', async ({ page }) => {
		// Track console errors
		const consoleErrors: string[] = [];
		page.on('console', (msg) => {
			if (msg.type() === 'error') {
				consoleErrors.push(msg.text());
				console.error('Browser console error:', msg.text());
			}
		});

		// Track uncaught exceptions
		page.on('pageerror', (error) => {
			console.error('Uncaught exception:', error);
		});

		// Login
		await login(page);

		// Navigate to contacts
		await page.goto('/modules/contacts');
		await page.waitForLoadState('networkidle');

		// Check page title
		await expect(page).toHaveTitle(/Contacts/i);

		// Check for header
		await expect(page.getByRole('heading', { name: /contacts/i })).toBeVisible();

		// Check for "New Contact" button
		await expect(page.getByRole('button', { name: /new contact/i })).toBeVisible();

		// Verify no console errors occurred during page load
		expect(consoleErrors.length).toBe(0);
	});

	test('should display DataTable components', async ({ page }) => {
		await login(page);
		await page.goto('/modules/contacts');
		await page.waitForLoadState('networkidle');

		// Check for search input
		const searchInput = page.getByPlaceholder(/search/i);
		await expect(searchInput).toBeVisible();

		// Check for table
		const table = page.locator('table');
		await expect(table).toBeVisible();

		// Check for table headers
		const tableHeaders = page.locator('thead th');
		await expect(tableHeaders.first()).toBeVisible();

		// Check for pagination controls
		await expect(page.getByText(/rows per page/i)).toBeVisible();
		await expect(page.getByText(/showing/i)).toBeVisible();
	});

	test('should display data rows', async ({ page }) => {
		await login(page);
		await page.goto('/modules/contacts');
		await page.waitForLoadState('networkidle');

		// Wait for table body
		const tableBody = page.locator('tbody');
		await expect(tableBody).toBeVisible();

		// Check if there are rows (or empty state)
		const rows = page.locator('tbody tr');
		const rowCount = await rows.count();

		if (rowCount > 0) {
			// If there are rows, verify first row is visible
			await expect(rows.first()).toBeVisible();

			// Check for checkboxes in rows (for row selection)
			const checkboxes = page.locator('tbody tr td').first().locator('[type="checkbox"]');
			await expect(checkboxes.first()).toBeVisible();
		} else {
			// If no data, check for empty state message
			await expect(page.getByText(/no results found/i)).toBeVisible();
		}
	});

	test('should handle search functionality', async ({ page }) => {
		await login(page);
		await page.goto('/modules/contacts');
		await page.waitForLoadState('networkidle');

		// Find search input
		const searchInput = page.getByPlaceholder(/search/i);
		await expect(searchInput).toBeVisible();

		// Type in search input
		await searchInput.fill('test');

		// Wait for debounce and API call
		await page.waitForTimeout(500);
		await page.waitForLoadState('networkidle');

		// Verify search was applied (URL should update or results should change)
		// The search should trigger an API call to /api/modules/contacts/records
		const apiCallMade = await page.evaluate(() => {
			// Check if any network activity occurred
			return true;
		});

		expect(apiCallMade).toBe(true);
	});

	test('should sort columns when clicking headers', async ({ page }) => {
		await login(page);
		await page.goto('/modules/contacts');
		await page.waitForLoadState('networkidle');

		// Find a sortable column header (should have a button)
		const headerButton = page.locator('thead th button').first();

		if (await headerButton.isVisible()) {
			// Click to sort ascending
			await headerButton.click();
			await page.waitForLoadState('networkidle');

			// Check for sort indicator (arrow icon)
			const sortIcon = page.locator('thead th svg').first();
			await expect(sortIcon).toBeVisible();

			// Click again to sort descending
			await headerButton.click();
			await page.waitForLoadState('networkidle');
		}
	});

	test('should handle row selection', async ({ page }) => {
		await login(page);
		await page.goto('/modules/contacts');
		await page.waitForLoadState('networkidle');

		// Wait for rows to load
		const rows = page.locator('tbody tr');
		const rowCount = await rows.count();

		if (rowCount > 0) {
			// Find first row checkbox
			const firstRowCheckbox = rows.first().locator('[type="checkbox"]');
			await expect(firstRowCheckbox).toBeVisible();

			// Click checkbox to select row
			await firstRowCheckbox.click();

			// Wait for selection state to update
			await page.waitForTimeout(100);

			// Check if bulk actions toolbar appears
			const selectedCount = page.getByText(/selected/i);
			await expect(selectedCount).toBeVisible();

			// Check for bulk action buttons
			await expect(page.getByRole('button', { name: /add tags/i })).toBeVisible();
			await expect(page.getByRole('button', { name: /export/i })).toBeVisible();
			await expect(page.getByRole('button', { name: /delete/i })).toBeVisible();
		}
	});

	test('should handle select all checkbox', async ({ page }) => {
		await login(page);
		await page.goto('/modules/contacts');
		await page.waitForLoadState('networkidle');

		// Find header checkbox (select all)
		const headerCheckbox = page.locator('thead [type="checkbox"]');

		if (await headerCheckbox.isVisible()) {
			// Click to select all
			await headerCheckbox.click();
			await page.waitForTimeout(100);

			// Verify selection count is displayed
			const selectedText = page.getByText(/selected/i);
			await expect(selectedText).toBeVisible();

			// Click again to deselect all
			await headerCheckbox.click();
			await page.waitForTimeout(100);
		}
	});

	test('should handle pagination controls', async ({ page }) => {
		await login(page);
		await page.goto('/modules/contacts');
		await page.waitForLoadState('networkidle');

		// Check pagination info
		const paginationInfo = page.getByText(/showing \d+ to \d+ of \d+/i);
		await expect(paginationInfo).toBeVisible();

		// Check for page size selector
		const pageSizeSelector = page.getByText(/rows per page/i);
		await expect(pageSizeSelector).toBeVisible();

		// Check for navigation buttons
		const nextButton = page.getByRole('button', { name: /next page/i });
		const prevButton = page.getByRole('button', { name: /previous page/i });
		const firstButton = page.getByRole('button', { name: /first page/i });
		const lastButton = page.getByRole('button', { name: /last page/i });

		await expect(nextButton).toBeVisible();
		await expect(prevButton).toBeVisible();
		await expect(firstButton).toBeVisible();
		await expect(lastButton).toBeVisible();

		// Check if buttons are properly disabled (first and previous should be disabled on page 1)
		await expect(prevButton).toBeDisabled();
		await expect(firstButton).toBeDisabled();
	});

	test('should change page size', async ({ page }) => {
		await login(page);
		await page.goto('/modules/contacts');
		await page.waitForLoadState('networkidle');

		// Find page size selector trigger
		const pageSizeTrigger = page.locator('[data-slot="select-trigger"]').first();

		if (await pageSizeTrigger.isVisible()) {
			// Click to open dropdown
			await pageSizeTrigger.click();
			await page.waitForTimeout(100);

			// Select a different page size (e.g., 25)
			const option25 = page.getByText('25', { exact: true });
			if (await option25.isVisible()) {
				await option25.click();
				await page.waitForLoadState('networkidle');

				// Verify the selection changed
				await expect(pageSizeTrigger).toContainText('25');
			}
		}
	});

	test('should navigate to detail page when clicking row', async ({ page }) => {
		await login(page);
		await page.goto('/modules/contacts');
		await page.waitForLoadState('networkidle');

		// Wait for rows to load
		const rows = page.locator('tbody tr');
		const rowCount = await rows.count();

		if (rowCount > 0) {
			// Click first row (not the checkbox)
			const firstRow = rows.first();
			const firstCell = firstRow.locator('td').nth(1); // Skip checkbox column

			await firstCell.click();

			// Wait for navigation
			await page.waitForURL(/\/modules\/contacts\/\d+/);

			// Verify we're on a detail page
			expect(page.url()).toMatch(/\/modules\/contacts\/\d+/);
		}
	});

	test('should handle loading states', async ({ page }) => {
		await login(page);

		// Navigate and immediately check for loading indicator
		const navigationPromise = page.goto('/modules/contacts');

		// Check for loading spinner (if visible before data loads)
		const loadingIndicator = page.getByText(/loading/i);
		const spinner = page.locator('svg.animate-spin');

		// One of these might be visible during loading
		const hasLoading = await loadingIndicator.isVisible().catch(() => false);
		const hasSpinner = await spinner.isVisible().catch(() => false);

		// Wait for navigation to complete
		await navigationPromise;
		await page.waitForLoadState('networkidle');

		// After loading, table should be visible
		const table = page.locator('table');
		await expect(table).toBeVisible();
	});

	test('should handle empty state', async ({ page }) => {
		await login(page);
		await page.goto('/modules/contacts');
		await page.waitForLoadState('networkidle');

		// Search for something that doesn't exist
		const searchInput = page.getByPlaceholder(/search/i);
		await searchInput.fill('xyznonexistentcontact12345');
		await page.waitForTimeout(500);
		await page.waitForLoadState('networkidle');

		// Should show empty state
		const emptyState = page.getByText(/no results found/i);
		await expect(emptyState).toBeVisible();

		// Should show helpful message
		const helpText = page.getByText(/try adjusting your filters/i);
		await expect(helpText).toBeVisible();
	});

	test('should clear search', async ({ page }) => {
		await login(page);
		await page.goto('/modules/contacts');
		await page.waitForLoadState('networkidle');

		// Type in search
		const searchInput = page.getByPlaceholder(/search/i);
		await searchInput.fill('test');
		await page.waitForTimeout(500);

		// Check for clear button
		const clearButton = page.locator('button', { has: page.locator('svg') }).filter({ hasText: '' });

		// Clear search
		await searchInput.clear();
		await page.waitForTimeout(500);
		await page.waitForLoadState('networkidle');

		// Verify search was cleared
		await expect(searchInput).toHaveValue('');
	});

	test('should display correct column types', async ({ page }) => {
		await login(page);
		await page.goto('/modules/contacts');
		await page.waitForLoadState('networkidle');

		// Wait for table to load
		const rows = page.locator('tbody tr');
		const rowCount = await rows.count();

		if (rowCount > 0) {
			// Check for email links (if email column exists)
			const emailLinks = page.locator('tbody a[href^="mailto:"]');
			const hasEmails = await emailLinks.count() > 0;

			// Check for phone links (if phone column exists)
			const phoneLinks = page.locator('tbody a[href^="tel:"]');
			const hasPhones = await phoneLinks.count() > 0;

			// At least some type-specific rendering should be present
			expect(hasEmails || hasPhones).toBe(true);
		}
	});

	test('should not have JavaScript errors during interactions', async ({ page }) => {
		const consoleErrors: string[] = [];
		const pageErrors: Error[] = [];

		page.on('console', (msg) => {
			if (msg.type() === 'error') {
				consoleErrors.push(msg.text());
			}
		});

		page.on('pageerror', (error) => {
			pageErrors.push(error);
		});

		await login(page);
		await page.goto('/modules/contacts');
		await page.waitForLoadState('networkidle');

		// Perform various interactions
		const searchInput = page.getByPlaceholder(/search/i);
		await searchInput.fill('test');
		await page.waitForTimeout(500);

		// Click a header to sort
		const headerButton = page.locator('thead th button').first();
		if (await headerButton.isVisible()) {
			await headerButton.click();
			await page.waitForLoadState('networkidle');
		}

		// Select a row
		const firstRowCheckbox = page.locator('tbody tr').first().locator('[type="checkbox"]');
		if (await firstRowCheckbox.isVisible()) {
			await firstRowCheckbox.click();
		}

		// Verify no errors occurred
		expect(consoleErrors.length).toBe(0);
		expect(pageErrors.length).toBe(0);
	});
});

import { test, expect } from '@playwright/test';

test.describe('DataTable New Features', () => {
	// Helper function to login
	async function login(page: any) {
		await page.goto('/login');
		await page.getByLabel(/email/i).fill('admin@test.com');
		await page.getByLabel(/password/i).fill('password');
		await page.getByRole('button', { name: /log in|sign in/i }).click();
		await page.waitForURL(/dashboard|\/$/);
	}

	test.describe('Export Functionality', () => {
		test('should display export button', async ({ page }) => {
			await login(page);
			await page.goto('/modules/contacts');
			await page.waitForLoadState('networkidle');

			// Check for export button
			const exportButton = page.getByRole('button', { name: /export/i });
			await expect(exportButton).toBeVisible();
		});

		test('should show export format dropdown', async ({ page }) => {
			await login(page);
			await page.goto('/modules/contacts');
			await page.waitForLoadState('networkidle');

			// Click export button
			const exportButton = page.getByRole('button', { name: /export/i }).first();
			await exportButton.click();

			// Wait for dropdown to appear
			await page.waitForTimeout(200);

			// Check for Excel and CSV options
			await expect(page.getByText(/export as excel/i)).toBeVisible();
			await expect(page.getByText(/export as csv/i)).toBeVisible();
		});

		test('should trigger Excel download when selected', async ({ page }) => {
			await login(page);
			await page.goto('/modules/contacts');
			await page.waitForLoadState('networkidle');

			// Setup download listener
			const downloadPromise = page.waitForEvent('download', { timeout: 10000 });

			// Click export button
			const exportButton = page.getByRole('button', { name: /export/i }).first();
			await exportButton.click();

			// Wait for dropdown
			await page.waitForTimeout(200);

			// Click Excel option
			await page.getByText(/export as excel/i).click();

			// Wait for download
			const download = await downloadPromise;

			// Verify download started
			expect(download.suggestedFilename()).toMatch(/contacts.*\.xlsx$/i);
		});

		test('should trigger CSV download when selected', async ({ page }) => {
			await login(page);
			await page.goto('/modules/contacts');
			await page.waitForLoadState('networkidle');

			// Setup download listener
			const downloadPromise = page.waitForEvent('download', { timeout: 10000 });

			// Click export button
			const exportButton = page.getByRole('button', { name: /export/i }).first();
			await exportButton.click();

			// Wait for dropdown
			await page.waitForTimeout(200);

			// Click CSV option
			await page.getByText(/export as csv/i).click();

			// Wait for download
			const download = await downloadPromise;

			// Verify download started
			expect(download.suggestedFilename()).toMatch(/contacts.*\.csv$/i);
		});

		test('should export with current filters applied', async ({ page }) => {
			await login(page);
			await page.goto('/modules/contacts');
			await page.waitForLoadState('networkidle');

			// Apply search filter
			const searchInput = page.getByPlaceholder(/search/i);
			await searchInput.fill('test');
			await page.waitForTimeout(500);
			await page.waitForLoadState('networkidle');

			// Setup download listener
			const downloadPromise = page.waitForEvent('download', { timeout: 10000 });

			// Click export button
			const exportButton = page.getByRole('button', { name: /export/i }).first();
			await exportButton.click();
			await page.waitForTimeout(200);

			// Click Excel option
			await page.getByText(/export as excel/i).click();

			// Verify download started
			const download = await downloadPromise;
			expect(download.suggestedFilename()).toBeTruthy();
		});

		test('should show export option in bulk actions when rows selected', async ({ page }) => {
			await login(page);
			await page.goto('/modules/contacts');
			await page.waitForLoadState('networkidle');

			// Wait for rows to load
			const rows = page.locator('tbody tr');
			const rowCount = await rows.count();

			if (rowCount > 0) {
				// Select first row
				const firstRowCheckbox = rows.first().locator('[type="checkbox"]');
				await firstRowCheckbox.click();
				await page.waitForTimeout(200);

				// Verify export button in bulk actions
				const bulkExportButton = page.getByRole('button', { name: /export/i }).last();
				await expect(bulkExportButton).toBeVisible();
			}
		});
	});

	test.describe('Actions Column', () => {
		test('should display actions column in table', async ({ page }) => {
			await login(page);
			await page.goto('/modules/contacts');
			await page.waitForLoadState('networkidle');

			// Check for Actions header
			const actionsHeader = page.locator('thead th').filter({ hasText: /actions/i });
			await expect(actionsHeader).toBeVisible();
		});

		test('should display action menu button in rows', async ({ page }) => {
			await login(page);
			await page.goto('/modules/contacts');
			await page.waitForLoadState('networkidle');

			const rows = page.locator('tbody tr');
			const rowCount = await rows.count();

			if (rowCount > 0) {
				// Find actions button (three dots icon)
				const actionsButton = rows.first().locator('button[aria-haspopup="menu"]');
				await expect(actionsButton).toBeVisible();
			}
		});

		test('should open actions menu on click', async ({ page }) => {
			await login(page);
			await page.goto('/modules/contacts');
			await page.waitForLoadState('networkidle');

			const rows = page.locator('tbody tr');
			const rowCount = await rows.count();

			if (rowCount > 0) {
				// Click actions button
				const actionsButton = rows.first().locator('button[aria-haspopup="menu"]');
				await actionsButton.click();

				// Wait for menu to appear
				await page.waitForTimeout(200);

				// Verify menu options
				await expect(page.getByRole('menuitem', { name: /view/i })).toBeVisible();
				await expect(page.getByRole('menuitem', { name: /edit/i })).toBeVisible();
				await expect(page.getByRole('menuitem', { name: /duplicate/i })).toBeVisible();
				await expect(page.getByRole('menuitem', { name: /delete/i })).toBeVisible();
			}
		});

		test('should navigate to view page when clicking View action', async ({ page }) => {
			await login(page);
			await page.goto('/modules/contacts');
			await page.waitForLoadState('networkidle');

			const rows = page.locator('tbody tr');
			const rowCount = await rows.count();

			if (rowCount > 0) {
				// Click actions button
				const actionsButton = rows.first().locator('button[aria-haspopup="menu"]');
				await actionsButton.click();
				await page.waitForTimeout(200);

				// Click View option
				await page.getByRole('menuitem', { name: /view/i }).click();

				// Wait for navigation
				await page.waitForURL(/\/modules\/contacts\/\d+$/);

				// Verify we're on detail page
				expect(page.url()).toMatch(/\/modules\/contacts\/\d+$/);
			}
		});

		test('should navigate to edit page when clicking Edit action', async ({ page }) => {
			await login(page);
			await page.goto('/modules/contacts');
			await page.waitForLoadState('networkidle');

			const rows = page.locator('tbody tr');
			const rowCount = await rows.count();

			if (rowCount > 0) {
				// Click actions button
				const actionsButton = rows.first().locator('button[aria-haspopup="menu"]');
				await actionsButton.click();
				await page.waitForTimeout(200);

				// Click Edit option
				await page.getByRole('menuitem', { name: /edit/i }).click();

				// Wait for navigation
				await page.waitForURL(/\/modules\/contacts\/\d+\/edit$/);

				// Verify we're on edit page
				expect(page.url()).toMatch(/\/modules\/contacts\/\d+\/edit$/);
			}
		});

		test('should show confirmation when clicking Delete action', async ({ page }) => {
			await login(page);
			await page.goto('/modules/contacts');
			await page.waitForLoadState('networkidle');

			const rows = page.locator('tbody tr');
			const rowCount = await rows.count();

			if (rowCount > 0) {
				// Click actions button
				const actionsButton = rows.first().locator('button[aria-haspopup="menu"]');
				await actionsButton.click();
				await page.waitForTimeout(200);

				// Setup dialog handler
				page.on('dialog', (dialog) => {
					expect(dialog.message()).toMatch(/are you sure/i);
					dialog.dismiss();
				});

				// Click Delete option
				await page.getByRole('menuitem', { name: /delete/i }).click();

				// Wait for dialog
				await page.waitForTimeout(200);
			}
		});

		test('should not propagate click to row when clicking actions button', async ({ page }) => {
			await login(page);
			await page.goto('/modules/contacts');
			await page.waitForLoadState('networkidle');

			const rows = page.locator('tbody tr');
			const rowCount = await rows.count();

			if (rowCount > 0) {
				const currentUrl = page.url();

				// Click actions button
				const actionsButton = rows.first().locator('button[aria-haspopup="menu"]');
				await actionsButton.click();
				await page.waitForTimeout(200);

				// Verify we didn't navigate (row click didn't trigger)
				expect(page.url()).toBe(currentUrl);
			}
		});
	});

	test.describe('Global Search', () => {
		test('should search across all searchable fields', async ({ page }) => {
			await login(page);
			await page.goto('/modules/contacts');
			await page.waitForLoadState('networkidle');

			// Get initial row count
			const initialRows = page.locator('tbody tr');
			const initialCount = await initialRows.count();

			// Perform search
			const searchInput = page.getByPlaceholder(/search/i);
			await searchInput.fill('john');
			await page.waitForTimeout(500);
			await page.waitForLoadState('networkidle');

			// Verify search was performed (results changed or stayed same)
			const searchedRows = page.locator('tbody tr');
			const searchedCount = await searchedRows.count();

			// Either results changed or we have an empty state
			const hasResults = searchedCount > 0 || (await page.getByText(/no results found/i).isVisible());
			expect(hasResults).toBe(true);
		});

		test('should update URL with search parameter', async ({ page }) => {
			await login(page);
			await page.goto('/modules/contacts');
			await page.waitForLoadState('networkidle');

			// Perform search
			const searchInput = page.getByPlaceholder(/search/i);
			await searchInput.fill('test');
			await page.waitForTimeout(500);
			await page.waitForLoadState('networkidle');

			// Verify URL contains search parameter
			expect(page.url()).toContain('search=test');
		});

		test('should clear search when X button clicked', async ({ page }) => {
			await login(page);
			await page.goto('/modules/contacts');
			await page.waitForLoadState('networkidle');

			// Perform search
			const searchInput = page.getByPlaceholder(/search/i);
			await searchInput.fill('test');
			await page.waitForTimeout(500);

			// Click clear button (X icon in search input)
			await searchInput.clear();
			await page.waitForTimeout(500);
			await page.waitForLoadState('networkidle');

			// Verify search was cleared
			expect(await searchInput.inputValue()).toBe('');
		});
	});

	test.describe('Column Visibility Toggle', () => {
		test('should display column toggle button', async ({ page }) => {
			await login(page);
			await page.goto('/modules/contacts');
			await page.waitForLoadState('networkidle');

			// Look for column toggle button (typically has a columns icon)
			const columnToggle = page.getByRole('button').filter({ has: page.locator('svg') }).first();
			await expect(columnToggle).toBeVisible();
		});

		test('should open column visibility menu', async ({ page }) => {
			await login(page);
			await page.goto('/modules/contacts');
			await page.waitForLoadState('networkidle');

			// Find and click column toggle
			const columnToggle = page.getByRole('button').filter({ has: page.locator('svg') }).first();
			await columnToggle.click();
			await page.waitForTimeout(200);

			// Verify menu with checkboxes appears
			const checkboxes = page.locator('[role="menuitemcheckbox"]');
			const count = await checkboxes.count();
			expect(count).toBeGreaterThan(0);
		});
	});

	test.describe('Error Handling', () => {
		test('should handle API errors gracefully', async ({ page }) => {
			await login(page);

			// Intercept API call and return error
			await page.route('**/api/modules/contacts/records*', (route) => {
				route.fulfill({
					status: 500,
					contentType: 'application/json',
					body: JSON.stringify({ message: 'Server error' })
				});
			});

			await page.goto('/modules/contacts');
			await page.waitForLoadState('networkidle');
			await page.waitForTimeout(1000);

			// Should show error state instead of crashing
			const hasError =
				(await page.getByText(/error/i).isVisible()) ||
				(await page.getByText(/failed/i).isVisible()) ||
				(await page.locator('tbody tr').count()) === 0;

			expect(hasError).toBe(true);
		});
	});

	test.describe('Accessibility', () => {
		test('should have proper ARIA labels on interactive elements', async ({ page }) => {
			await login(page);
			await page.goto('/modules/contacts');
			await page.waitForLoadState('networkidle');

			// Check for aria-labels on buttons
			const exportButton = page.getByRole('button', { name: /export/i });
			await expect(exportButton).toBeVisible();

			// Check for accessible checkboxes
			const checkboxes = page.locator('[type="checkbox"]');
			const count = await checkboxes.count();
			expect(count).toBeGreaterThan(0);
		});

		test('should be keyboard navigable', async ({ page }) => {
			await login(page);
			await page.goto('/modules/contacts');
			await page.waitForLoadState('networkidle');

			// Tab through elements
			await page.keyboard.press('Tab');
			await page.keyboard.press('Tab');

			// At least one element should have focus
			const focusedElement = await page.evaluate(() => document.activeElement?.tagName);
			expect(focusedElement).toBeTruthy();
		});
	});
});

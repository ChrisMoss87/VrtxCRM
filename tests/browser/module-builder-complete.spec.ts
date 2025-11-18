import { expect, test } from './fixtures/auth.fixture';

test.describe('Module Builder - Complete Test Suite', () => {
	let moduleId: string;

	test('create module and navigate to edit page', async ({ authenticatedPage: page }) => {
		// Create a new module
		await page.goto('/admin/modules/create');
		await page.waitForLoadState('networkidle');

		await page.fill('#name', 'Test Module');
		await page.fill('#singular_name', 'Test Item');
		await page.fill('#description', 'Comprehensive test module');

		// Click an icon
		await page.locator('button[title="Users"]').first().click();

		await page.click('button[type="submit"]');

		// Wait for redirect to edit page and extract module ID
		await page.waitForURL(/\/admin\/modules\/\d+\/edit/);
		const url = page.url();
		moduleId = url.match(/\/admin\/modules\/(\d+)\/edit/)?.[1] || '';
		expect(moduleId).toBeTruthy();

		await expect(page.locator('h1')).toContainText('Edit Module');
	});

	test('validation works correctly', async ({ authenticatedPage: page }) => {
		await page.goto(`/admin/modules/${moduleId}/edit`);
		await page.waitForLoadState('networkidle');

		// Go to Fields & Blocks tab
		await page.click('button:has-text("Fields & Blocks")');
		await page.waitForTimeout(500);

		// Try to validate with no blocks (should fail)
		await page.click('button:has-text("Validate")');
		await page.waitForTimeout(500);

		// Should see validation error
		await expect(page.locator('text=Module must have at least one block')).toBeVisible();

		// Add a block
		await page.locator('[data-testid="add-block-button"]').click();
		await page.waitForTimeout(300);

		// Validate again (should warn about empty block)
		await page.click('button:has-text("Validate")');
		await page.waitForTimeout(500);

		// Should see warning about no fields
		await expect(page.locator('text=has no fields')).toBeVisible();
	});

	test('field templates picker works', async ({ authenticatedPage: page }) => {
		await page.goto(`/admin/modules/${moduleId}/edit`);
		await page.waitForLoadState('networkidle');

		await page.click('button:has-text("Fields & Blocks")');
		await page.waitForTimeout(500);

		// Ensure we have a block
		const blockItems = page.locator('[data-testid="block-item"]');
		const blockCount = await blockItems.count();
		if (blockCount === 0) {
			await page.locator('[data-testid="add-block-button"]').click();
			await page.waitForTimeout(300);
		}

		// Open template picker
		await page.click('button:has-text("Browse Templates")');
		await page.waitForTimeout(500);

		// Should see template dialog
		await expect(page.locator('text=Choose a Field Template')).toBeVisible();

		// Search for email template
		await page.fill('input[placeholder="Search templates..."]', 'email');
		await page.waitForTimeout(300);

		// Click on email template
		await page.click('button:has-text("Email Address")');
		await page.waitForTimeout(500);

		// Verify field was added
		const fieldItems = page.locator('[data-testid="field-item"]');
		await expect(fieldItems).toHaveCount(1);

		// Verify the field has correct values
		const apiNameInput = fieldItems.first().locator('input[placeholder="api_name"]');
		const apiName = await apiNameInput.inputValue();
		expect(apiName).toBe('email');
	});

	test('live preview panel toggles correctly', async ({ authenticatedPage: page }) => {
		await page.goto(`/admin/modules/${moduleId}/edit`);
		await page.waitForLoadState('networkidle');

		await page.click('button:has-text("Fields & Blocks")');
		await page.waitForTimeout(500);

		// Preview should be visible by default
		await expect(page.locator('text=Live Preview')).toBeVisible();

		// Hide preview
		await page.click('button:has-text("Hide Preview")');
		await page.waitForTimeout(300);

		// Preview should be hidden
		await expect(page.locator('text=Live Preview')).not.toBeVisible();

		// Show preview again
		await page.click('button:has-text("Show Preview")');
		await page.waitForTimeout(300);

		// Preview should be visible
		await expect(page.locator('text=Live Preview')).toBeVisible();
	});

	test('field type-specific settings work', async ({ authenticatedPage: page }) => {
		await page.goto(`/admin/modules/${moduleId}/edit`);
		await page.waitForLoadState('networkidle');

		await page.click('button:has-text("Fields & Blocks")');
		await page.waitForTimeout(500);

		// Add a number field
		await page.locator('[data-testid="add-field-button"]').first().click();
		await page.waitForTimeout(300);

		const fieldItems = page.locator('[data-testid="field-item"]');
		const lastField = fieldItems.last();

		// Set field label
		await lastField.locator('input[placeholder="Field Label"]').fill('Quantity');

		// Change type to number
		await lastField.locator('select').first().selectOption('number');
		await page.waitForTimeout(300);

		// Should see min/max value inputs
		await expect(lastField.locator('text=Minimum Value')).toBeVisible();
		await expect(lastField.locator('text=Maximum Value')).toBeVisible();

		// Fill in min/max
		await lastField.locator('input[placeholder="No minimum"]').fill('0');
		await lastField.locator('input[placeholder="No maximum"]').fill('100');
	});

	test('select field options can be added', async ({ authenticatedPage: page }) => {
		await page.goto(`/admin/modules/${moduleId}/edit`);
		await page.waitForLoadState('networkidle');

		await page.click('button:has-text("Fields & Blocks")');
		await page.waitForTimeout(500);

		// Add a select field
		await page.locator('[data-testid="add-field-button"]').first().click();
		await page.waitForTimeout(300);

		const fieldItems = page.locator('[data-testid="field-item"]');
		const lastField = fieldItems.last();

		// Set field label
		await lastField.locator('input[placeholder="Field Label"]').fill('Status');

		// Change type to select
		await lastField.locator('select').first().selectOption('select');
		await page.waitForTimeout(300);

		// Should see options section
		await expect(lastField.locator('text=Options')).toBeVisible();

		// Add an option
		await lastField.locator('button:has-text("Add Option")').click();
		await page.waitForTimeout(200);

		// Fill in option details
		const optionInputs = lastField.locator('input[placeholder="Label"]');
		await optionInputs.first().fill('Active');

		const valueInputs = lastField.locator('input[placeholder="Value"]');
		await valueInputs.first().fill('active');

		// Add another option
		await lastField.locator('button:has-text("Add Option")').click();
		await page.waitForTimeout(200);

		await optionInputs.nth(1).fill('Inactive');
		await valueInputs.nth(1).fill('inactive');
	});

	test('keyboard shortcuts work', async ({ authenticatedPage: page }) => {
		await page.goto(`/admin/modules/${moduleId}/edit`);
		await page.waitForLoadState('networkidle');

		await page.click('button:has-text("Fields & Blocks")');
		await page.waitForTimeout(500);

		// Ensure there's at least one field
		const fieldItems = page.locator('[data-testid="field-item"]');
		const fieldCount = await fieldItems.count();
		if (fieldCount === 0) {
			await page.locator('[data-testid="add-field-button"]').first().click();
			await page.waitForTimeout(300);
		}

		// Test Cmd/Ctrl + S to save
		await page.keyboard.press('Control+s');
		await page.waitForTimeout(1000);

		// Should see success message
		await expect(page.locator('text=saved successfully')).toBeVisible({ timeout: 5000 });

		// Test Cmd/Ctrl + Shift + V to validate
		await page.keyboard.press('Control+Shift+V');
		await page.waitForTimeout(500);

		// Should see validation panel
		await expect(page.locator('text=Validation')).toBeVisible();

		// Test Cmd/Ctrl + Shift + P to toggle preview
		const previewBefore = await page.locator('text=Live Preview').isVisible();
		await page.keyboard.press('Control+Shift+P');
		await page.waitForTimeout(500);

		const previewAfter = await page.locator('text=Live Preview').isVisible();
		expect(previewBefore).not.toBe(previewAfter);
	});

	test('can save and load module structure', async ({ authenticatedPage: page }) => {
		await page.goto(`/admin/modules/${moduleId}/edit`);
		await page.waitForLoadState('networkidle');

		await page.click('button:has-text("Fields & Blocks")');
		await page.waitForTimeout(500);

		// Save current structure
		await page.locator('button:has-text("Save Fields & Blocks")').click();
		await page.waitForTimeout(1000);

		// Reload page
		await page.reload();
		await page.waitForLoadState('networkidle');

		await page.click('button:has-text("Fields & Blocks")');
		await page.waitForTimeout(500);

		// Verify structure persisted
		const blockItems = page.locator('[data-testid="block-item"]');
		await expect(blockItems).toHaveCount(1); // We added 1 block earlier

		const fieldItems = page.locator('[data-testid="field-item"]');
		const count = await fieldItems.count();
		expect(count).toBeGreaterThan(0); // Should have at least the fields we added
	});

	test('basic module info can be updated', async ({ authenticatedPage: page }) => {
		await page.goto(`/admin/modules/${moduleId}/edit`);
		await page.waitForLoadState('networkidle');

		// Should be on Basic Info tab by default
		await expect(page.locator('#name')).toBeVisible();

		// Update name
		await page.fill('#name', 'Updated Test Module');
		await page.fill('#description', 'Updated description');

		// Save
		await page.locator('button:has-text("Save Changes")').click();
		await page.waitForTimeout(1000);

		// Should see success message
		await expect(page.locator('text=Module updated successfully')).toBeVisible();

		// Reload and verify
		await page.reload();
		await page.waitForLoadState('networkidle');

		const nameValue = await page.inputValue('#name');
		expect(nameValue).toBe('Updated Test Module');
	});

	test('module can be activated/deactivated', async ({ authenticatedPage: page }) => {
		await page.goto(`/admin/modules/${moduleId}/edit`);
		await page.waitForLoadState('networkidle');

		// Toggle is_active switch
		const activeSwitch = page.locator('#is_active');
		const initialState = await activeSwitch.isChecked();

		await activeSwitch.click();
		await page.waitForTimeout(300);

		// Save
		await page.locator('button:has-text("Save Changes")').click();
		await page.waitForTimeout(1000);

		// Reload and verify state changed
		await page.reload();
		await page.waitForLoadState('networkidle');

		const newState = await page.locator('#is_active').isChecked();
		expect(newState).not.toBe(initialState);
	});
});

test.describe('Module Builder - Field Validation', () => {
	test('validates duplicate API names', async ({ authenticatedPage: page }) => {
		// Create a fresh module for this test
		await page.goto('/admin/modules/create');
		await page.waitForLoadState('networkidle');

		await page.fill('#name', 'Validation Test');
		await page.fill('#singular_name', 'Validation Item');
		await page.click('button[type="submit"]');

		await page.waitForURL(/\/admin\/modules\/\d+\/edit/);
		await page.click('button:has-text("Fields & Blocks")');
		await page.waitForTimeout(500);

		// Add block
		await page.locator('[data-testid="add-block-button"]').click();
		await page.waitForTimeout(300);

		// Add two fields with same API name
		await page.locator('[data-testid="add-field-button"]').click();
		await page.waitForTimeout(300);

		const fields = page.locator('[data-testid="field-item"]');
		await fields.first().locator('input[placeholder="Field Label"]').fill('Field One');
		await fields.first().locator('input[placeholder="api_name"]').fill('duplicate_name');

		await page.locator('[data-testid="add-field-button"]').click();
		await page.waitForTimeout(300);

		await fields.nth(1).locator('input[placeholder="Field Label"]').fill('Field Two');
		await fields.nth(1).locator('input[placeholder="api_name"]').fill('duplicate_name');

		// Validate
		await page.click('button:has-text("Validate")');
		await page.waitForTimeout(500);

		// Should see error about duplicate API names
		await expect(page.locator('text=API name "duplicate_name" is used by multiple fields')).toBeVisible();
	});

	test('validates select fields must have options', async ({ authenticatedPage: page }) => {
		await page.goto('/admin/modules/create');
		await page.waitForLoadState('networkidle');

		await page.fill('#name', 'Select Validation Test');
		await page.fill('#singular_name', 'Select Item');
		await page.click('button[type="submit"]');

		await page.waitForURL(/\/admin\/modules\/\d+\/edit/);
		await page.click('button:has-text("Fields & Blocks")');
		await page.waitForTimeout(500);

		// Add block and field
		await page.locator('[data-testid="add-block-button"]').click();
		await page.waitForTimeout(300);

		await page.locator('[data-testid="add-field-button"]').click();
		await page.waitForTimeout(300);

		// Make it a select field but don't add options
		const field = page.locator('[data-testid="field-item"]').first();
		await field.locator('input[placeholder="Field Label"]').fill('Status');
		await field.locator('select').first().selectOption('select');
		await page.waitForTimeout(300);

		// Validate
		await page.click('button:has-text("Validate")');
		await page.waitForTimeout(500);

		// Should see error about missing options
		await expect(page.locator('text=must have at least one option')).toBeVisible();
	});
});

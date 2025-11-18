/**
 * Module validation utilities
 * Validates module structure before publishing
 */

interface ValidationError {
	type: 'error' | 'warning';
	field?: string;
	message: string;
	blockIndex?: number;
	fieldIndex?: number;
}

interface Block {
	id?: number;
	type: string;
	label: string;
	order: number;
	settings: Record<string, any>;
	fields: Field[];
}

interface FieldOption {
	id?: number;
	label: string;
	value: string;
	color: string | null;
	order: number;
	is_default: boolean;
}

interface Field {
	id?: number;
	type: string;
	api_name: string;
	label: string;
	description: string | null;
	help_text: string | null;
	is_required: boolean;
	is_unique: boolean;
	is_searchable: boolean;
	order: number;
	default_value: string | null;
	validation_rules: Record<string, any>;
	settings: Record<string, any>;
	width: number;
	options?: FieldOption[];
}

interface Module {
	id?: number;
	name: string;
	singular_name: string;
	api_name?: string;
	icon: string | null;
	description: string | null;
	is_active: boolean;
	is_system: boolean;
	settings: Record<string, any>;
	blocks: Block[];
}

export interface ValidationResult {
	valid: boolean;
	errors: ValidationError[];
	warnings: ValidationError[];
}

/**
 * Validate a module before publishing
 */
export function validateModule(module: Module): ValidationResult {
	const errors: ValidationError[] = [];
	const warnings: ValidationError[] = [];

	// Basic module validation
	if (!module.name || module.name.trim() === '') {
		errors.push({
			type: 'error',
			field: 'name',
			message: 'Module name is required',
		});
	}

	if (!module.singular_name || module.singular_name.trim() === '') {
		errors.push({
			type: 'error',
			field: 'singular_name',
			message: 'Singular name is required',
		});
	}

	// Must have at least one block
	if (!module.blocks || module.blocks.length === 0) {
		errors.push({
			type: 'error',
			message: 'Module must have at least one block',
		});
		return { valid: false, errors, warnings };
	}

	// Must have at least one field total
	const totalFields = module.blocks.reduce((sum, block) => sum + (block.fields?.length || 0), 0);
	if (totalFields === 0) {
		errors.push({
			type: 'error',
			message: 'Module must have at least one field',
		});
	}

	// Track API names for uniqueness check
	const apiNames = new Set<string>();

	// Validate each block
	module.blocks.forEach((block, blockIndex) => {
		// Block must have a label
		if (!block.label || block.label.trim() === '') {
			errors.push({
				type: 'error',
				blockIndex,
				message: `Block ${blockIndex + 1} must have a label`,
			});
		}

		// Warn if block has no fields
		if (!block.fields || block.fields.length === 0) {
			warnings.push({
				type: 'warning',
				blockIndex,
				message: `Block "${block.label || `Block ${blockIndex + 1}`}" has no fields`,
			});
		}

		// Validate each field in the block
		block.fields?.forEach((field, fieldIndex) => {
			// Field must have a label
			if (!field.label || field.label.trim() === '') {
				errors.push({
					type: 'error',
					blockIndex,
					fieldIndex,
					message: `Field at position ${fieldIndex + 1} in block "${block.label}" must have a label`,
				});
			}

			// Field must have an API name
			if (!field.api_name || field.api_name.trim() === '') {
				errors.push({
					type: 'error',
					blockIndex,
					fieldIndex,
					field: field.label,
					message: `Field "${field.label || `Field ${fieldIndex + 1}`}" must have an API name`,
				});
			} else {
				// API name must be unique
				if (apiNames.has(field.api_name)) {
					errors.push({
						type: 'error',
						blockIndex,
						fieldIndex,
						field: field.label,
						message: `API name "${field.api_name}" is used by multiple fields. API names must be unique.`,
					});
				}
				apiNames.add(field.api_name);

				// API name must be valid (lowercase, underscore, starts with letter)
				if (!/^[a-z][a-z0-9_]*$/.test(field.api_name)) {
					errors.push({
						type: 'error',
						blockIndex,
						fieldIndex,
						field: field.label,
						message: `API name "${field.api_name}" is invalid. Must start with a letter and contain only lowercase letters, numbers, and underscores.`,
					});
				}
			}

			// Field must have a type
			if (!field.type || field.type.trim() === '') {
				errors.push({
					type: 'error',
					blockIndex,
					fieldIndex,
					field: field.label,
					message: `Field "${field.label}" must have a type`,
				});
			}

			// Validate field type-specific requirements
			if (['select', 'radio', 'multiselect'].includes(field.type)) {
				// These field types must have at least one option
				if (!field.options || field.options.length === 0) {
					errors.push({
						type: 'error',
						blockIndex,
						fieldIndex,
						field: field.label,
						message: `Field "${field.label}" (${field.type}) must have at least one option`,
					});
				} else {
					// Validate options
					const optionValues = new Set<string>();
					field.options.forEach((option, optIndex) => {
						if (!option.label || option.label.trim() === '') {
							errors.push({
								type: 'error',
								blockIndex,
								fieldIndex,
								field: field.label,
								message: `Option ${optIndex + 1} in field "${field.label}" must have a label`,
							});
						}
						if (!option.value || option.value.trim() === '') {
							errors.push({
								type: 'error',
								blockIndex,
								fieldIndex,
								field: field.label,
								message: `Option ${optIndex + 1} in field "${field.label}" must have a value`,
							});
						} else {
							// Check for duplicate option values
							if (optionValues.has(option.value)) {
								errors.push({
									type: 'error',
									blockIndex,
									fieldIndex,
									field: field.label,
									message: `Duplicate option value "${option.value}" in field "${field.label}"`,
								});
							}
							optionValues.add(option.value);
						}
					});
				}
			}

			// Validate number field constraints
			if (['number', 'decimal', 'currency', 'percent'].includes(field.type)) {
				if (field.settings?.min !== undefined && field.settings?.max !== undefined) {
					const min = parseFloat(field.settings.min);
					const max = parseFloat(field.settings.max);
					if (!isNaN(min) && !isNaN(max) && min > max) {
						errors.push({
							type: 'error',
							blockIndex,
							fieldIndex,
							field: field.label,
							message: `Field "${field.label}": minimum value (${min}) cannot be greater than maximum value (${max})`,
						});
					}
				}
			}

			// Validate text field constraints
			if (['text', 'textarea'].includes(field.type)) {
				if (field.settings?.min_length !== undefined && field.settings?.max_length !== undefined) {
					const minLen = parseInt(String(field.settings.min_length));
					const maxLen = parseInt(String(field.settings.max_length));
					if (!isNaN(minLen) && !isNaN(maxLen) && minLen > maxLen) {
						errors.push({
							type: 'error',
							blockIndex,
							fieldIndex,
							field: field.label,
							message: `Field "${field.label}": minimum length (${minLen}) cannot be greater than maximum length (${maxLen})`,
						});
					}
				}
			}

			// Validate date field constraints
			if (['date', 'datetime'].includes(field.type)) {
				if (field.settings?.min_date && field.settings?.max_date) {
					const minDate = new Date(field.settings.min_date);
					const maxDate = new Date(field.settings.max_date);
					if (minDate > maxDate) {
						errors.push({
							type: 'error',
							blockIndex,
							fieldIndex,
							field: field.label,
							message: `Field "${field.label}": minimum date cannot be after maximum date`,
						});
					}
				}
			}

			// Warning: Unique fields should probably be required
			if (field.is_unique && !field.is_required) {
				warnings.push({
					type: 'warning',
					blockIndex,
					fieldIndex,
					field: field.label,
					message: `Field "${field.label}" is marked as unique but not required. Consider making it required to ensure all records have a unique value.`,
				});
			}
		});
	});

	return {
		valid: errors.length === 0,
		errors,
		warnings,
	};
}

/**
 * Get a user-friendly error summary
 */
export function getValidationSummary(result: ValidationResult): string {
	const { errors, warnings } = result;
	const parts: string[] = [];

	if (errors.length > 0) {
		parts.push(`${errors.length} error${errors.length !== 1 ? 's' : ''} found`);
	}

	if (warnings.length > 0) {
		parts.push(`${warnings.length} warning${warnings.length !== 1 ? 's' : ''} found`);
	}

	return parts.join(', ');
}

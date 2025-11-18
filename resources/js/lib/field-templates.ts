/**
 * Pre-built field templates for common use cases
 */

export interface FieldTemplate {
	id: string;
	name: string;
	description: string;
	category: 'text' | 'contact' | 'business' | 'datetime' | 'numeric' | 'other';
	icon: string;
	field: {
		type: string;
		label: string;
		api_name: string;
		description: string | null;
		help_text: string | null;
		is_required: boolean;
		is_unique: boolean;
		is_searchable: boolean;
		default_value: string | null;
		validation_rules: Record<string, any>;
		settings: Record<string, any>;
		width: number;
		options?: Array<{
			label: string;
			value: string;
			color: string | null;
			order: number;
			is_default: boolean;
		}>;
	};
}

export const fieldTemplates: FieldTemplate[] = [
	// Text Fields
	{
		id: 'first_name',
		name: 'First Name',
		description: 'Standard first name field',
		category: 'text',
		icon: 'User',
		field: {
			type: 'text',
			label: 'First Name',
			api_name: 'first_name',
			description: null,
			help_text: null,
			is_required: true,
			is_unique: false,
			is_searchable: true,
			default_value: null,
			validation_rules: {},
			settings: { max_length: 100 },
			width: 50,
		},
	},
	{
		id: 'last_name',
		name: 'Last Name',
		description: 'Standard last name field',
		category: 'text',
		icon: 'User',
		field: {
			type: 'text',
			label: 'Last Name',
			api_name: 'last_name',
			description: null,
			help_text: null,
			is_required: true,
			is_unique: false,
			is_searchable: true,
			default_value: null,
			validation_rules: {},
			settings: { max_length: 100 },
			width: 50,
		},
	},
	{
		id: 'company',
		name: 'Company',
		description: 'Company or organization name',
		category: 'business',
		icon: 'Building2',
		field: {
			type: 'text',
			label: 'Company',
			api_name: 'company',
			description: null,
			help_text: null,
			is_required: false,
			is_unique: false,
			is_searchable: true,
			default_value: null,
			validation_rules: {},
			settings: { max_length: 200 },
			width: 100,
		},
	},
	{
		id: 'job_title',
		name: 'Job Title',
		description: 'Position or role',
		category: 'business',
		icon: 'Briefcase',
		field: {
			type: 'text',
			label: 'Job Title',
			api_name: 'job_title',
			description: null,
			help_text: null,
			is_required: false,
			is_unique: false,
			is_searchable: true,
			default_value: null,
			validation_rules: {},
			settings: { max_length: 100 },
			width: 50,
		},
	},

	// Contact Fields
	{
		id: 'email',
		name: 'Email Address',
		description: 'Standard email field with validation',
		category: 'contact',
		icon: 'Mail',
		field: {
			type: 'email',
			label: 'Email',
			api_name: 'email',
			description: null,
			help_text: null,
			is_required: true,
			is_unique: true,
			is_searchable: true,
			default_value: null,
			validation_rules: {},
			settings: {},
			width: 50,
		},
	},
	{
		id: 'phone',
		name: 'Phone Number',
		description: 'Standard phone field',
		category: 'contact',
		icon: 'Phone',
		field: {
			type: 'phone',
			label: 'Phone',
			api_name: 'phone',
			description: null,
			help_text: null,
			is_required: false,
			is_unique: false,
			is_searchable: true,
			default_value: null,
			validation_rules: {},
			settings: {},
			width: 50,
		},
	},
	{
		id: 'website',
		name: 'Website',
		description: 'Website URL field',
		category: 'contact',
		icon: 'Globe',
		field: {
			type: 'url',
			label: 'Website',
			api_name: 'website',
			description: null,
			help_text: null,
			is_required: false,
			is_unique: false,
			is_searchable: false,
			default_value: null,
			validation_rules: {},
			settings: {},
			width: 50,
		},
	},
	{
		id: 'address',
		name: 'Address',
		description: 'Full address field',
		category: 'contact',
		icon: 'MapPin',
		field: {
			type: 'textarea',
			label: 'Address',
			api_name: 'address',
			description: null,
			help_text: 'Full street address',
			is_required: false,
			is_unique: false,
			is_searchable: false,
			default_value: null,
			validation_rules: {},
			settings: { rows: 3 },
			width: 100,
		},
	},

	// Numeric Fields
	{
		id: 'amount',
		name: 'Amount',
		description: 'Currency amount field',
		category: 'numeric',
		icon: 'DollarSign',
		field: {
			type: 'currency',
			label: 'Amount',
			api_name: 'amount',
			description: null,
			help_text: null,
			is_required: false,
			is_unique: false,
			is_searchable: false,
			default_value: '0.00',
			validation_rules: {},
			settings: { min: 0 },
			width: 50,
		},
	},
	{
		id: 'quantity',
		name: 'Quantity',
		description: 'Numeric quantity field',
		category: 'numeric',
		icon: 'Hash',
		field: {
			type: 'number',
			label: 'Quantity',
			api_name: 'quantity',
			description: null,
			help_text: null,
			is_required: false,
			is_unique: false,
			is_searchable: false,
			default_value: '1',
			validation_rules: {},
			settings: { min: 0 },
			width: 33,
		},
	},
	{
		id: 'percentage',
		name: 'Percentage',
		description: 'Percentage field',
		category: 'numeric',
		icon: 'Percent',
		field: {
			type: 'percent',
			label: 'Percentage',
			api_name: 'percentage',
			description: null,
			help_text: null,
			is_required: false,
			is_unique: false,
			is_searchable: false,
			default_value: '0',
			validation_rules: {},
			settings: { min: 0, max: 100 },
			width: 33,
		},
	},

	// Date/Time Fields
	{
		id: 'due_date',
		name: 'Due Date',
		description: 'Due date field',
		category: 'datetime',
		icon: 'Calendar',
		field: {
			type: 'date',
			label: 'Due Date',
			api_name: 'due_date',
			description: null,
			help_text: null,
			is_required: false,
			is_unique: false,
			is_searchable: false,
			default_value: null,
			validation_rules: {},
			settings: {},
			width: 50,
		},
	},
	{
		id: 'start_date',
		name: 'Start Date',
		description: 'Start date field',
		category: 'datetime',
		icon: 'CalendarDays',
		field: {
			type: 'date',
			label: 'Start Date',
			api_name: 'start_date',
			description: null,
			help_text: null,
			is_required: false,
			is_unique: false,
			is_searchable: false,
			default_value: null,
			validation_rules: {},
			settings: {},
			width: 50,
		},
	},
	{
		id: 'end_date',
		name: 'End Date',
		description: 'End date field',
		category: 'datetime',
		icon: 'CalendarCheck',
		field: {
			type: 'date',
			label: 'End Date',
			api_name: 'end_date',
			description: null,
			help_text: null,
			is_required: false,
			is_unique: false,
			is_searchable: false,
			default_value: null,
			validation_rules: {},
			settings: {},
			width: 50,
		},
	},

	// Status Fields
	{
		id: 'status',
		name: 'Status',
		description: 'Generic status dropdown',
		category: 'other',
		icon: 'CheckCircle',
		field: {
			type: 'select',
			label: 'Status',
			api_name: 'status',
			description: null,
			help_text: null,
			is_required: true,
			is_unique: false,
			is_searchable: true,
			default_value: 'active',
			validation_rules: {},
			settings: {},
			width: 50,
			options: [
				{ label: 'Active', value: 'active', color: 'green', order: 0, is_default: true },
				{ label: 'Inactive', value: 'inactive', color: 'gray', order: 1, is_default: false },
				{ label: 'Pending', value: 'pending', color: 'yellow', order: 2, is_default: false },
			],
		},
	},
	{
		id: 'priority',
		name: 'Priority',
		description: 'Priority level field',
		category: 'other',
		icon: 'Flag',
		field: {
			type: 'select',
			label: 'Priority',
			api_name: 'priority',
			description: null,
			help_text: null,
			is_required: false,
			is_unique: false,
			is_searchable: true,
			default_value: 'medium',
			validation_rules: {},
			settings: {},
			width: 33,
			options: [
				{ label: 'Low', value: 'low', color: 'gray', order: 0, is_default: false },
				{ label: 'Medium', value: 'medium', color: 'blue', order: 1, is_default: true },
				{ label: 'High', value: 'high', color: 'orange', order: 2, is_default: false },
				{ label: 'Urgent', value: 'urgent', color: 'red', order: 3, is_default: false },
			],
		},
	},

	// Description/Notes
	{
		id: 'description',
		name: 'Description',
		description: 'Multi-line description field',
		category: 'text',
		icon: 'FileText',
		field: {
			type: 'textarea',
			label: 'Description',
			api_name: 'description',
			description: null,
			help_text: null,
			is_required: false,
			is_unique: false,
			is_searchable: true,
			default_value: null,
			validation_rules: {},
			settings: { rows: 4 },
			width: 100,
		},
	},
	{
		id: 'notes',
		name: 'Notes',
		description: 'Internal notes field',
		category: 'text',
		icon: 'StickyNote',
		field: {
			type: 'textarea',
			label: 'Notes',
			api_name: 'notes',
			description: null,
			help_text: 'Internal notes (not visible to customers)',
			is_required: false,
			is_unique: false,
			is_searchable: true,
			default_value: null,
			validation_rules: {},
			settings: { rows: 3 },
			width: 100,
		},
	},
];

export function getTemplatesByCategory(category: string): FieldTemplate[] {
	return fieldTemplates.filter((t) => t.category === category);
}

export function getTemplateById(id: string): FieldTemplate | undefined {
	return fieldTemplates.find((t) => t.id === id);
}

export const templateCategories = [
	{ value: 'text', label: 'Text & Content' },
	{ value: 'contact', label: 'Contact Information' },
	{ value: 'business', label: 'Business' },
	{ value: 'datetime', label: 'Date & Time' },
	{ value: 'numeric', label: 'Numbers & Currency' },
	{ value: 'other', label: 'Other' },
];

/**
 * TypeScript type definitions for the dynamic module system
 */

export interface Module {
	id: number;
	name: string;
	api_name: string;
	icon: string;
	is_system: boolean;
	settings: Record<string, any>;
	created_at: string;
	updated_at?: string;
	blocks?: Block[];
}

export interface Block {
	id: number;
	name: string;
	type: 'section' | 'tab' | 'accordion';
	settings: Record<string, any>;
	order: number;
	fields: Field[];
}

export interface Field {
	id: number;
	label: string;
	api_name: string;
	type: FieldType;
	is_required: boolean;
	is_unique: boolean;
	settings: Record<string, any>;
	validation_rules: string[] | null;
	default_value: any;
	help_text: string | null;
	order: number;
	options?: FieldOption[];
}

export type FieldType =
	| 'text'
	| 'textarea'
	| 'rich_text'
	| 'email'
	| 'phone'
	| 'url'
	| 'number'
	| 'decimal'
	| 'currency'
	| 'percent'
	| 'date'
	| 'datetime'
	| 'time'
	| 'select'
	| 'multiselect'
	| 'radio'
	| 'checkbox'
	| 'toggle'
	| 'lookup'
	| 'formula'
	| 'file'
	| 'image';

export interface FieldOption {
	id: number;
	label: string;
	value: string;
	color: string | null;
	order: number;
}

export interface ModuleRecord {
	id: number;
	module_id: number;
	data: Record<string, any>;
	created_at: string;
	updated_at: string;
}

export interface PaginatedRecords {
	data: ModuleRecord[];
	meta: {
		current_page: number;
		from: number | null;
		last_page: number;
		per_page: number;
		to: number | null;
		total: number;
	};
}

export interface ModuleListPageProps {
	module: Module;
	records: PaginatedRecords;
}

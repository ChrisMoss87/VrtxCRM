<?php

declare(strict_types=1);

namespace App\Services;

use App\Infrastructure\Persistence\Eloquent\Models\FieldModel;
use App\Infrastructure\Persistence\Eloquent\Models\FieldOptionModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

final class FieldService
{
    /**
     * Supported field types.
     */
    public const FIELD_TYPES = [
        'text',
        'textarea',
        'number',
        'decimal',
        'email',
        'phone',
        'url',
        'select',
        'multiselect',
        'radio',
        'checkbox',
        'toggle',
        'date',
        'datetime',
        'time',
        'currency',
        'percent',
        'lookup',
        'formula',
        'file',
        'image',
        'richtext',
    ];

    /**
     * Field types that require options.
     */
    public const FIELDS_WITH_OPTIONS = [
        'select',
        'multiselect',
        'radio',
    ];

    /**
     * Create a new field.
     *
     * @param  array{block_id: int, type: string, label: string, api_name?: string, description?: string, help_text?: string, is_required?: bool, is_unique?: bool, is_searchable?: bool, order?: int, default_value?: string, validation_rules?: array, settings?: array, width?: int, options?: array}  $data
     * @throws RuntimeException If field creation fails
     */
    public function createField(array $data): FieldModel
    {
        // Validate field type
        if (! in_array($data['type'], self::FIELD_TYPES)) {
            throw new RuntimeException("Invalid field type: {$data['type']}");
        }

        // Generate api_name if not provided
        $apiName = $data['api_name'] ?? Str::snake($data['label']);
        $this->validateFieldApiName($data['block_id'], $apiName);

        DB::beginTransaction();

        try {
            $field = FieldModel::create([
                'block_id' => $data['block_id'],
                'type' => $data['type'],
                'api_name' => $apiName,
                'label' => $data['label'],
                'description' => $data['description'] ?? null,
                'help_text' => $data['help_text'] ?? null,
                'is_required' => $data['is_required'] ?? false,
                'is_unique' => $data['is_unique'] ?? false,
                'is_searchable' => $data['is_searchable'] ?? false,
                'order' => $data['order'] ?? 0,
                'default_value' => $data['default_value'] ?? null,
                'validation_rules' => $data['validation_rules'] ?? [],
                'settings' => $data['settings'] ?? [],
                'width' => $data['width'] ?? 100,
            ]);

            // Create options if field type requires them
            if (in_array($data['type'], self::FIELDS_WITH_OPTIONS) && ! empty($data['options'])) {
                foreach ($data['options'] as $optionData) {
                    $this->createFieldOption($field->id, $optionData);
                }
            }

            DB::commit();

            return $field->fresh(['options']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw new RuntimeException("Failed to create field: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Update an existing field.
     *
     * @throws RuntimeException If field update fails
     */
    public function updateField(int $fieldId, array $data): FieldModel
    {
        DB::beginTransaction();

        try {
            $field = FieldModel::findOrFail($fieldId);

            // Check if module is system module
            if ($field->block->module->is_system) {
                throw new RuntimeException('Cannot modify fields in system modules.');
            }

            // Validate type if being changed
            if (isset($data['type']) && ! in_array($data['type'], self::FIELD_TYPES)) {
                throw new RuntimeException("Invalid field type: {$data['type']}");
            }

            // Validate api_name if being changed
            if (isset($data['api_name']) && $data['api_name'] !== $field->api_name) {
                $this->validateFieldApiName($field->block_id, $data['api_name'], $fieldId);
            }

            $field->update([
                'type' => $data['type'] ?? $field->type,
                'api_name' => $data['api_name'] ?? $field->api_name,
                'label' => $data['label'] ?? $field->label,
                'description' => $data['description'] ?? $field->description,
                'help_text' => $data['help_text'] ?? $field->help_text,
                'is_required' => $data['is_required'] ?? $field->is_required,
                'is_unique' => $data['is_unique'] ?? $field->is_unique,
                'is_searchable' => $data['is_searchable'] ?? $field->is_searchable,
                'order' => $data['order'] ?? $field->order,
                'default_value' => $data['default_value'] ?? $field->default_value,
                'validation_rules' => $data['validation_rules'] ?? $field->validation_rules,
                'settings' => $data['settings'] ?? $field->settings,
                'width' => $data['width'] ?? $field->width,
            ]);

            DB::commit();

            return $field->fresh(['options']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw new RuntimeException("Failed to update field: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Delete a field.
     *
     * @throws RuntimeException If field deletion fails
     */
    public function deleteField(int $fieldId): void
    {
        DB::beginTransaction();

        try {
            $field = FieldModel::findOrFail($fieldId);

            if ($field->block->module->is_system) {
                throw new RuntimeException('Cannot delete fields from system modules.');
            }

            // Delete will cascade to field options
            $field->delete();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new RuntimeException("Failed to delete field: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Create a field option.
     *
     * @param  array{label: string, value: string, color?: string, order?: int, is_default?: bool}  $data
     * @throws RuntimeException If option creation fails
     */
    public function createFieldOption(int $fieldId, array $data): FieldOptionModel
    {
        $field = FieldModel::findOrFail($fieldId);

        // Validate field type supports options
        if (! in_array($field->type, self::FIELDS_WITH_OPTIONS)) {
            throw new RuntimeException("Field type '{$field->type}' does not support options.");
        }

        DB::beginTransaction();

        try {
            // If this is being set as default, unset other defaults
            if ($data['is_default'] ?? false) {
                FieldOptionModel::where('field_id', $fieldId)
                    ->update(['is_default' => false]);
            }

            $option = FieldOptionModel::create([
                'field_id' => $fieldId,
                'label' => $data['label'],
                'value' => $data['value'],
                'color' => $data['color'] ?? null,
                'order' => $data['order'] ?? 0,
                'is_default' => $data['is_default'] ?? false,
            ]);

            DB::commit();

            return $option;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new RuntimeException("Failed to create field option: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Update a field option.
     *
     * @throws RuntimeException If option update fails
     */
    public function updateFieldOption(int $optionId, array $data): FieldOptionModel
    {
        DB::beginTransaction();

        try {
            $option = FieldOptionModel::findOrFail($optionId);

            // If setting as default, unset other defaults
            if (($data['is_default'] ?? false) && ! $option->is_default) {
                FieldOptionModel::where('field_id', $option->field_id)
                    ->where('id', '!=', $optionId)
                    ->update(['is_default' => false]);
            }

            $option->update([
                'label' => $data['label'] ?? $option->label,
                'value' => $data['value'] ?? $option->value,
                'color' => $data['color'] ?? $option->color,
                'order' => $data['order'] ?? $option->order,
                'is_default' => $data['is_default'] ?? $option->is_default,
            ]);

            DB::commit();

            return $option;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new RuntimeException("Failed to update field option: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Delete a field option.
     *
     * @throws RuntimeException If option deletion fails
     */
    public function deleteFieldOption(int $optionId): void
    {
        DB::beginTransaction();

        try {
            $option = FieldOptionModel::findOrFail($optionId);
            $option->delete();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new RuntimeException("Failed to delete field option: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Reorder fields within a block.
     *
     * @param  array<int, int>  $order  Array of field_id => order
     */
    public function reorderFields(int $blockId, array $order): void
    {
        DB::beginTransaction();

        try {
            // Verify all fields belong to this block
            $fieldIds = array_keys($order);
            $validFields = FieldModel::where('block_id', $blockId)
                ->whereIn('id', $fieldIds)
                ->count();

            if ($validFields !== count($fieldIds)) {
                throw new RuntimeException('Invalid field IDs provided.');
            }

            foreach ($order as $fieldId => $position) {
                FieldModel::where('id', $fieldId)->update(['order' => $position]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new RuntimeException("Failed to reorder fields: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Reorder field options.
     *
     * @param  array<int, int>  $order  Array of option_id => order
     */
    public function reorderFieldOptions(int $fieldId, array $order): void
    {
        DB::beginTransaction();

        try {
            // Verify all options belong to this field
            $optionIds = array_keys($order);
            $validOptions = FieldOptionModel::where('field_id', $fieldId)
                ->whereIn('id', $optionIds)
                ->count();

            if ($validOptions !== count($optionIds)) {
                throw new RuntimeException('Invalid option IDs provided.');
            }

            foreach ($order as $optionId => $position) {
                FieldOptionModel::where('id', $optionId)->update(['order' => $position]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new RuntimeException("Failed to reorder field options: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Get field with options.
     */
    public function getField(int $fieldId): FieldModel
    {
        return FieldModel::with(['options', 'block.module'])
            ->findOrFail($fieldId);
    }

    /**
     * Get all fields for a block.
     */
    public function getFieldsForBlock(int $blockId): iterable
    {
        return FieldModel::where('block_id', $blockId)
            ->with('options')
            ->orderBy('order')
            ->orderBy('label')
            ->get();
    }

    /**
     * Validate field api_name is unique within block.
     *
     * @throws RuntimeException If api_name is taken
     */
    private function validateFieldApiName(int $blockId, string $apiName, ?int $excludeFieldId = null): void
    {
        $query = FieldModel::where('block_id', $blockId)
            ->where('api_name', $apiName);

        if ($excludeFieldId) {
            $query->where('id', '!=', $excludeFieldId);
        }

        if ($query->exists()) {
            throw new RuntimeException("Field with api_name '{$apiName}' already exists in this block.");
        }
    }

    /**
     * Validate field data against field definition.
     *
     * @throws RuntimeException If validation fails
     */
    public function validateFieldValue(FieldModel $field, mixed $value): void
    {
        // Required check
        if ($field->is_required && ($value === null || $value === '')) {
            throw new RuntimeException("Field '{$field->label}' is required.");
        }

        // Skip validation if value is null and not required
        if ($value === null || $value === '') {
            return;
        }

        // Type-specific validation
        match ($field->type) {
            'email' => $this->validateEmail($value, $field->label),
            'url' => $this->validateUrl($value, $field->label),
            'phone' => $this->validatePhone($value, $field->label),
            'number', 'decimal', 'currency', 'percent' => $this->validateNumeric($value, $field->label),
            'date' => $this->validateDate($value, $field->label),
            'datetime' => $this->validateDateTime($value, $field->label),
            'select', 'radio' => $this->validateOption($field, $value),
            'multiselect' => $this->validateMultipleOptions($field, $value),
            default => null,
        };
    }

    /**
     * Validate email format.
     */
    private function validateEmail(mixed $value, string $fieldLabel): void
    {
        if (! filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException("Field '{$fieldLabel}' must be a valid email address.");
        }
    }

    /**
     * Validate URL format.
     */
    private function validateUrl(mixed $value, string $fieldLabel): void
    {
        if (! filter_var($value, FILTER_VALIDATE_URL)) {
            throw new RuntimeException("Field '{$fieldLabel}' must be a valid URL.");
        }
    }

    /**
     * Validate phone format (basic validation).
     */
    private function validatePhone(mixed $value, string $fieldLabel): void
    {
        if (! preg_match('/^[+]?[(]?[0-9]{1,4}[)]?[-\s\.]?[(]?[0-9]{1,4}[)]?[-\s\.]?[0-9]{1,9}$/', $value)) {
            throw new RuntimeException("Field '{$fieldLabel}' must be a valid phone number.");
        }
    }

    /**
     * Validate numeric value.
     */
    private function validateNumeric(mixed $value, string $fieldLabel): void
    {
        if (! is_numeric($value)) {
            throw new RuntimeException("Field '{$fieldLabel}' must be a number.");
        }
    }

    /**
     * Validate date format.
     */
    private function validateDate(mixed $value, string $fieldLabel): void
    {
        $date = \DateTime::createFromFormat('Y-m-d', $value);
        if (! $date || $date->format('Y-m-d') !== $value) {
            throw new RuntimeException("Field '{$fieldLabel}' must be a valid date (Y-m-d).");
        }
    }

    /**
     * Validate datetime format.
     */
    private function validateDateTime(mixed $value, string $fieldLabel): void
    {
        $datetime = \DateTime::createFromFormat('Y-m-d H:i:s', $value);
        if (! $datetime) {
            throw new RuntimeException("Field '{$fieldLabel}' must be a valid datetime (Y-m-d H:i:s).");
        }
    }

    /**
     * Validate option exists for field.
     */
    private function validateOption(FieldModel $field, mixed $value): void
    {
        $exists = $field->options()
            ->where('value', $value)
            ->exists();

        if (! $exists) {
            throw new RuntimeException("Invalid option value for field '{$field->label}'.");
        }
    }

    /**
     * Validate multiple options exist for field.
     */
    private function validateMultipleOptions(FieldModel $field, mixed $value): void
    {
        if (! is_array($value)) {
            throw new RuntimeException("Field '{$field->label}' must be an array of options.");
        }

        $validOptions = $field->options()->pluck('value')->toArray();

        foreach ($value as $option) {
            if (! in_array($option, $validOptions)) {
                throw new RuntimeException("Invalid option value '{$option}' for field '{$field->label}'.");
            }
        }
    }
}

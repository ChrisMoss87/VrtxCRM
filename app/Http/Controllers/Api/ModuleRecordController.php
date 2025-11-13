<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\Models\ModuleModel;
use App\Infrastructure\Persistence\Eloquent\Models\ModuleRecordModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

final class ModuleRecordController extends Controller
{
    /**
     * Get paginated records for a module.
     */
    public function index(Request $request, string $moduleApiName): JsonResponse
    {
        $module = ModuleModel::with(['blocks.fields'])->where('api_name', $moduleApiName)->firstOrFail();

        $perPage = (int) $request->query('per_page', 50);
        $page = (int) $request->query('page', 1);

        $query = ModuleRecordModel::where('module_id', $module->id);

        // Get valid field names for validation
        $validFields = $module->blocks->flatMap->fields->pluck('api_name')->toArray();

        // Handle global search
        if ($search = $request->query('search')) {
            $query->where('data', 'like', '%'.$search.'%');
        }

        // Handle filters (JSON array of {field, operator, value})
        if ($filters = $request->query('filters')) {
            if (is_string($filters)) {
                $filters = json_decode($filters, true);
            }

            if (is_array($filters)) {
                foreach ($filters as $filter) {
                    if (! isset($filter['field'], $filter['operator'], $filter['value'])) {
                        continue;
                    }

                    $field = $filter['field'];
                    $operator = $filter['operator'];
                    $value = $filter['value'];

                    // Validate field exists in module
                    if (! in_array($field, $validFields, true)) {
                        continue;
                    }

                    // Apply filter based on operator
                    match ($operator) {
                        'equals' => $query->whereRaw("data->>'$.{$field}' = ?", [$value]),
                        'not_equals' => $query->whereRaw("data->>'$.{$field}' != ?", [$value]),
                        'contains' => $query->whereRaw("data->>'$.{$field}' LIKE ?", ["%{$value}%"]),
                        'not_contains' => $query->whereRaw("data->>'$.{$field}' NOT LIKE ?", ["%{$value}%"]),
                        'starts_with' => $query->whereRaw("data->>'$.{$field}' LIKE ?", ["{$value}%"]),
                        'ends_with' => $query->whereRaw("data->>'$.{$field}' LIKE ?", ["%{$value}"]),
                        'gt' => $query->whereRaw("CAST(data->>'$.{$field}' AS DECIMAL(20,2)) > ?", [$value]),
                        'gte' => $query->whereRaw("CAST(data->>'$.{$field}' AS DECIMAL(20,2)) >= ?", [$value]),
                        'lt' => $query->whereRaw("CAST(data->>'$.{$field}' AS DECIMAL(20,2)) < ?", [$value]),
                        'lte' => $query->whereRaw("CAST(data->>'$.{$field}' AS DECIMAL(20,2)) <= ?", [$value]),
                        'between' => is_array($value) && count($value) === 2
                            ? $query->whereRaw(
                                "CAST(data->>'$.{$field}' AS DECIMAL(20,2)) BETWEEN ? AND ?",
                                [$value[0], $value[1]]
                            )
                            : null,
                        'in' => is_array($value)
                            ? $query->whereRaw(
                                "data->>'$.{$field}' IN (".implode(',', array_fill(0, count($value), '?')).')',
                                $value
                            )
                            : null,
                        'not_in' => is_array($value)
                            ? $query->whereRaw(
                                "data->>'$.{$field}' NOT IN (".implode(',', array_fill(0, count($value), '?')).')',
                                $value
                            )
                            : null,
                        'is_null' => $query->whereRaw("data->>'$.{$field}' IS NULL"),
                        'is_not_null' => $query->whereRaw("data->>'$.{$field}' IS NOT NULL"),
                        default => null,
                    };
                }
            }
        }

        // Handle sorting (JSON array of {field, direction})
        if ($sort = $request->query('sort')) {
            if (is_string($sort)) {
                $sort = json_decode($sort, true);
            }

            if (is_array($sort)) {
                foreach ($sort as $sortConfig) {
                    if (! isset($sortConfig['field'], $sortConfig['direction'])) {
                        continue;
                    }

                    $field = $sortConfig['field'];
                    $direction = mb_strtolower($sortConfig['direction']);

                    // Validate field and direction
                    if (! in_array($field, $validFields, true)) {
                        continue;
                    }

                    if (! in_array($direction, ['asc', 'desc'], true)) {
                        continue;
                    }

                    // Handle special fields
                    if (in_array($field, ['id', 'created_at', 'updated_at'], true)) {
                        $query->orderBy($field, $direction);
                    } else {
                        // Sort by JSON field
                        $query->orderByRaw("data->>'$.{$field}' {$direction}");
                    }
                }
            }
        } else {
            // Default sort by created_at desc
            $query->orderBy('created_at', 'desc');
        }

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'from' => $paginator->firstItem(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'to' => $paginator->lastItem(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    /**
     * Get a single record by ID.
     */
    public function show(string $moduleApiName, int $id): JsonResponse
    {
        $module = ModuleModel::where('api_name', $moduleApiName)->firstOrFail();

        $record = ModuleRecordModel::where('module_id', $module->id)
            ->where('id', $id)
            ->firstOrFail();

        return response()->json([
            'data' => $record,
        ]);
    }

    /**
     * Create a new record.
     */
    public function store(Request $request, string $moduleApiName): JsonResponse
    {
        $module = ModuleModel::with(['blocks.fields.relationship.toModule'])->where('api_name', $moduleApiName)->firstOrFail();

        // Build validation rules from module fields
        $rules = $this->buildValidationRules($module);

        try {
            $validated = $request->validate($rules);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        // Create the record
        $record = ModuleRecordModel::create([
            'module_id' => $module->id,
            'data' => $validated,
        ]);

        return response()->json([
            'data' => $record,
            'message' => ucfirst($module->name).' created successfully',
        ], 201);
    }

    /**
     * Update an existing record.
     */
    public function update(Request $request, string $moduleApiName, int $id): JsonResponse
    {
        $module = ModuleModel::with(['blocks.fields.relationship.toModule'])->where('api_name', $moduleApiName)->firstOrFail();

        $record = ModuleRecordModel::where('module_id', $module->id)
            ->where('id', $id)
            ->firstOrFail();

        // Build validation rules from module fields
        $rules = $this->buildValidationRules($module);

        try {
            $validated = $request->validate($rules);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        // Update the record
        $record->update([
            'data' => $validated,
        ]);

        return response()->json([
            'data' => $record,
            'message' => ucfirst($module->name).' updated successfully',
        ]);
    }

    /**
     * Delete a record.
     */
    public function destroy(string $moduleApiName, int $id): JsonResponse
    {
        $module = ModuleModel::where('api_name', $moduleApiName)->firstOrFail();

        $record = ModuleRecordModel::where('module_id', $module->id)
            ->where('id', $id)
            ->firstOrFail();

        // Handle related records
        $relatedRecordsService = app(\App\Services\RelatedRecordsService::class);

        // Cascade delete related records if configured
        $relatedRecordsService->handleCascadeDelete($module->id, $record->id);

        // Clean up orphaned references in other records
        $relatedRecordsService->cleanupOrphanedReferences($module->id, $record->id);

        // Delete the record
        $record->delete();

        return response()->json([
            'message' => ucfirst($module->name).' deleted successfully',
        ]);
    }

    /**
     * Build Laravel validation rules from module field definitions.
     */
    private function buildValidationRules(ModuleModel $module): array
    {
        $rules = [];

        foreach ($module->blocks as $block) {
            foreach ($block->fields as $field) {
                $fieldRules = [];

                // Required validation
                if ($field->is_required) {
                    $fieldRules[] = 'required';
                } else {
                    $fieldRules[] = 'nullable';
                }

                // Type-based validation
                switch ($field->type) {
                    case 'email':
                        $fieldRules[] = 'email';
                        break;
                    case 'url':
                        $fieldRules[] = 'url';
                        break;
                    case 'number':
                    case 'decimal':
                    case 'currency':
                    case 'percent':
                        $fieldRules[] = 'numeric';
                        break;
                    case 'date':
                        $fieldRules[] = 'date';
                        break;
                    case 'datetime':
                        $fieldRules[] = 'date';
                        break;
                    case 'select':
                    case 'radio':
                        // Validate against field options
                        $values = $field->options->pluck('value')->toArray();
                        if (! empty($values)) {
                            $fieldRules[] = 'in:'.implode(',', $values);
                        }
                        break;
                    case 'multiselect':
                        $fieldRules[] = 'array';
                        break;
                    case 'checkbox':
                    case 'toggle':
                        $fieldRules[] = 'boolean';
                        break;
                    case 'lookup':
                        // Validate lookup field based on relationship
                        if ($field->relationship) {
                            $relatedModule = $field->relationship->toModule;

                            // For one-to-many: single integer (related record ID)
                            if ($field->relationship->type === 'one_to_many') {
                                $fieldRules[] = 'integer';
                                $fieldRules[] = "exists:module_records,id,module_id,{$relatedModule->id}";
                            }
                            // For many-to-many: array of integers
                            elseif ($field->relationship->type === 'many_to_many') {
                                $fieldRules[] = 'array';
                                $fieldRules[] = "exists:module_records,id,module_id,{$relatedModule->id}";
                            }
                        } else {
                            $fieldRules[] = 'integer';
                        }
                        break;
                    default:
                        $fieldRules[] = 'string';
                }

                // Unique validation
                if ($field->is_unique) {
                    $fieldRules[] = 'unique:module_records,data->'.$field->api_name;
                }

                // Custom validation rules from field settings
                if (! empty($field->validation_rules)) {
                    if (is_array($field->validation_rules)) {
                        $fieldRules = array_merge($fieldRules, $field->validation_rules);
                    }
                }

                $rules[$field->api_name] = $fieldRules;
            }
        }

        return $rules;
    }
}

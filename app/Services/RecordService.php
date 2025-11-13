<?php

declare(strict_types=1);

namespace App\Services;

use App\Infrastructure\Persistence\Eloquent\Models\ModuleModel;
use App\Infrastructure\Persistence\Eloquent\Models\ModuleRecordModel;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class RecordService
{
    public function __construct(
        private readonly FieldService $fieldService
    ) {}

    /**
     * Create a new record for a module.
     *
     * @param  array<string, mixed>  $data  Field values keyed by api_name
     *
     * @throws RuntimeException If record creation fails
     */
    public function createRecord(int $moduleId, array $data, ?int $createdBy = null): ModuleRecordModel
    {
        $module = ModuleModel::with(['blocks.fields.options'])->findOrFail($moduleId);

        if (! $module->is_active) {
            throw new RuntimeException('Cannot create records for inactive modules.');
        }

        DB::beginTransaction();

        try {
            // Validate and transform data
            $validatedData = $this->validateAndTransformData($module, $data);

            // Create record
            $record = ModuleRecordModel::create([
                'module_id' => $moduleId,
                'data' => $validatedData,
                'created_by' => $createdBy ?? auth()->id(),
                'updated_by' => $createdBy ?? auth()->id(),
            ]);

            DB::commit();

            return $record->fresh(['module', 'creator', 'updater']);
        } catch (Exception $e) {
            DB::rollBack();
            throw new RuntimeException("Failed to create record: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Update an existing record.
     *
     * @param  array<string, mixed>  $data  Field values keyed by api_name
     *
     * @throws RuntimeException If record update fails
     */
    public function updateRecord(int $recordId, array $data, ?int $updatedBy = null): ModuleRecordModel
    {
        DB::beginTransaction();

        try {
            $record = ModuleRecordModel::with(['module.blocks.fields.options'])->findOrFail($recordId);

            if (! $record->module->is_active) {
                throw new RuntimeException('Cannot update records for inactive modules.');
            }

            // Validate and transform data
            $validatedData = $this->validateAndTransformData($record->module, $data, $record->data);

            // Update record
            $record->update([
                'data' => $validatedData,
                'updated_by' => $updatedBy ?? auth()->id(),
            ]);

            DB::commit();

            return $record->fresh(['module', 'creator', 'updater']);
        } catch (Exception $e) {
            DB::rollBack();
            throw new RuntimeException("Failed to update record: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Delete a record (soft delete).
     *
     * @throws RuntimeException If record deletion fails
     */
    public function deleteRecord(int $recordId): void
    {
        DB::beginTransaction();

        try {
            $record = ModuleRecordModel::findOrFail($recordId);
            $record->delete();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw new RuntimeException("Failed to delete record: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Restore a soft-deleted record.
     *
     * @throws RuntimeException If record restoration fails
     */
    public function restoreRecord(int $recordId): ModuleRecordModel
    {
        DB::beginTransaction();

        try {
            $record = ModuleRecordModel::withTrashed()->findOrFail($recordId);
            $record->restore();

            DB::commit();

            return $record->fresh(['module', 'creator', 'updater']);
        } catch (Exception $e) {
            DB::rollBack();
            throw new RuntimeException("Failed to restore record: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Permanently delete a record.
     *
     * @throws RuntimeException If record deletion fails
     */
    public function forceDeleteRecord(int $recordId): void
    {
        DB::beginTransaction();

        try {
            $record = ModuleRecordModel::withTrashed()->findOrFail($recordId);
            $record->forceDelete();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw new RuntimeException("Failed to permanently delete record: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Get a single record by ID.
     */
    public function getRecord(int $recordId): ModuleRecordModel
    {
        return ModuleRecordModel::with(['module.blocks.fields.options', 'creator', 'updater'])
            ->findOrFail($recordId);
    }

    /**
     * Get all records for a module with pagination.
     *
     * @param  array{search?: string, filters?: array, sort?: string, direction?: string}  $options
     */
    public function getRecords(
        int $moduleId,
        int $perPage = 15,
        array $options = []
    ): LengthAwarePaginator {
        $query = ModuleRecordModel::where('module_id', $moduleId)
            ->with(['creator', 'updater']);

        // Apply search if provided
        if (! empty($options['search'])) {
            $search = $options['search'];
            $query->where(function ($q) use ($search) {
                // Search in JSON data using PostgreSQL operators
                $q->whereRaw('data::text ILIKE ?', ["%{$search}%"]);
            });
        }

        // Apply filters if provided
        if (! empty($options['filters'])) {
            foreach ($options['filters'] as $field => $value) {
                $query->whereRaw('data->? = ?', [$field, json_encode($value)]);
            }
        }

        // Apply sorting
        $sortField = $options['sort'] ?? 'created_at';
        $direction = $options['direction'] ?? 'desc';

        if ($sortField === 'created_at' || $sortField === 'updated_at') {
            $query->orderBy($sortField, $direction);
        } else {
            // Sort by JSON field
            $query->orderByRaw("data->? {$direction}", [$sortField]);
        }

        return $query->paginate($perPage);
    }

    /**
     * Search records across multiple fields.
     *
     * @param  array<string>  $searchableFields  Field api_names to search
     */
    public function searchRecords(
        int $moduleId,
        string $searchTerm,
        array $searchableFields = [],
        int $perPage = 15
    ): LengthAwarePaginator {
        $query = ModuleRecordModel::where('module_id', $moduleId)
            ->with(['creator', 'updater']);

        if (empty($searchableFields)) {
            // Search all fields
            $query->whereRaw('data::text ILIKE ?', ["%{$searchTerm}%"]);
        } else {
            // Search specific fields
            $query->where(function ($q) use ($searchableFields, $searchTerm) {
                foreach ($searchableFields as $field) {
                    $q->orWhereRaw('data->>? ILIKE ?', [$field, "%{$searchTerm}%"]);
                }
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Get records with specific field value.
     */
    public function getRecordsByField(
        int $moduleId,
        string $fieldApiName,
        mixed $value,
        int $perPage = 15
    ): LengthAwarePaginator {
        return ModuleRecordModel::where('module_id', $moduleId)
            ->whereRaw('data->? = ?', [$fieldApiName, json_encode($value)])
            ->with(['creator', 'updater'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get record count for a module.
     */
    public function getRecordCount(int $moduleId): int
    {
        return ModuleRecordModel::where('module_id', $moduleId)->count();
    }

    /**
     * Get record count by field value.
     */
    public function getRecordCountByField(int $moduleId, string $fieldApiName, mixed $value): int
    {
        return ModuleRecordModel::where('module_id', $moduleId)
            ->whereRaw('data->? = ?', [$fieldApiName, json_encode($value)])
            ->count();
    }

    /**
     * Bulk create records.
     *
     * @param  array<array<string, mixed>>  $records  Array of record data
     * @return array<ModuleRecordModel>
     *
     * @throws RuntimeException If bulk creation fails
     */
    public function bulkCreateRecords(int $moduleId, array $records, ?int $createdBy = null): array
    {
        $module = ModuleModel::with(['blocks.fields.options'])->findOrFail($moduleId);

        if (! $module->is_active) {
            throw new RuntimeException('Cannot create records for inactive modules.');
        }

        DB::beginTransaction();

        try {
            $createdRecords = [];

            foreach ($records as $data) {
                $validatedData = $this->validateAndTransformData($module, $data);

                $record = ModuleRecordModel::create([
                    'module_id' => $moduleId,
                    'data' => $validatedData,
                    'created_by' => $createdBy ?? auth()->id(),
                    'updated_by' => $createdBy ?? auth()->id(),
                ]);

                $createdRecords[] = $record;
            }

            DB::commit();

            return $createdRecords;
        } catch (Exception $e) {
            DB::rollBack();
            throw new RuntimeException("Failed to bulk create records: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Bulk delete records.
     *
     * @param  array<int>  $recordIds
     *
     * @throws RuntimeException If bulk deletion fails
     */
    public function bulkDeleteRecords(array $recordIds): int
    {
        DB::beginTransaction();

        try {
            $deleted = ModuleRecordModel::whereIn('id', $recordIds)->delete();

            DB::commit();

            return $deleted;
        } catch (Exception $e) {
            DB::rollBack();
            throw new RuntimeException("Failed to bulk delete records: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Bulk update records with same data.
     *
     * @param  array<int>  $recordIds
     * @param  array<string, mixed>  $data
     *
     * @throws RuntimeException If bulk update fails
     */
    public function bulkUpdateRecords(int $moduleId, array $recordIds, array $data, ?int $updatedBy = null): int
    {
        DB::beginTransaction();

        try {
            $module = ModuleModel::with(['blocks.fields.options'])->findOrFail($moduleId);

            // Get all records to update
            $records = ModuleRecordModel::where('module_id', $moduleId)
                ->whereIn('id', $recordIds)
                ->get();

            if ($records->isEmpty()) {
                throw new RuntimeException('No records found to update.');
            }

            foreach ($records as $record) {
                // Merge new data with existing data
                $validatedData = $this->validateAndTransformData($module, $data, $record->data);

                $record->update([
                    'data' => $validatedData,
                    'updated_by' => $updatedBy ?? auth()->id(),
                ]);
            }

            DB::commit();

            return $records->count();
        } catch (Exception $e) {
            DB::rollBack();
            throw new RuntimeException("Failed to bulk update records: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Export records to array format.
     *
     * @return array<array<string, mixed>>
     */
    public function exportRecords(int $moduleId): array
    {
        $records = ModuleRecordModel::where('module_id', $moduleId)
            ->orderBy('created_at', 'desc')
            ->get();

        return $records->map(function ($record) {
            return array_merge(
                $record->data,
                [
                    'id' => $record->id,
                    'created_at' => $record->created_at->toIso8601String(),
                    'updated_at' => $record->updated_at->toIso8601String(),
                ]
            );
        })->toArray();
    }

    /**
     * Get unique values for a specific field.
     *
     * @return array<mixed>
     */
    public function getUniqueFieldValues(int $moduleId, string $fieldApiName): array
    {
        $records = ModuleRecordModel::where('module_id', $moduleId)
            ->get();

        return $records->pluck("data.{$fieldApiName}")
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Get record statistics for a module.
     */
    public function getRecordStatistics(int $moduleId): array
    {
        $total = $this->getRecordCount($moduleId);
        $deleted = ModuleRecordModel::where('module_id', $moduleId)->onlyTrashed()->count();

        $recent = ModuleRecordModel::where('module_id', $moduleId)
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        $updated = ModuleRecordModel::where('module_id', $moduleId)
            ->where('updated_at', '>=', now()->subDays(7))
            ->count();

        return [
            'total' => $total,
            'active' => $total - $deleted,
            'deleted' => $deleted,
            'created_last_7_days' => $recent,
            'updated_last_7_days' => $updated,
        ];
    }

    /**
     * Validate and transform data according to module field definitions.
     *
     * @param  array<string, mixed>  $data  New data
     * @param  array<string, mixed>  $existingData  Existing data (for updates)
     * @return array<string, mixed>
     */
    private function validateAndTransformData(ModuleModel $module, array $data, array $existingData = []): array
    {
        $validatedData = $existingData;

        // Get all fields from all blocks
        foreach ($module->blocks as $block) {
            foreach ($block->fields as $field) {
                $apiName = $field->api_name;

                // If field is present in data, validate it
                if (array_key_exists($apiName, $data)) {
                    $value = $data[$apiName];

                    // Validate field value
                    $this->fieldService->validateFieldValue($field, $value);

                    // Store validated value
                    $validatedData[$apiName] = $value;
                } elseif ($field->is_required && ! isset($existingData[$apiName])) {
                    // Field is required but not provided and not in existing data
                    throw new RuntimeException("Field '{$field->label}' is required.");
                } elseif (! isset($existingData[$apiName]) && $field->default_value !== null) {
                    // Use default value if not provided and not in existing data
                    $validatedData[$apiName] = $field->default_value;
                }
            }
        }

        return $validatedData;
    }
}

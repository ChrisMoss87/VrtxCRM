<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\Modules\Entities\ModuleRecord;
use App\Domain\Modules\Repositories\ModuleRecordRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\ModuleModel;
use Exception;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class RecordService
{
    public function __construct(
        private readonly ModuleRecordRepositoryInterface $recordRepository,
        private readonly FieldService $fieldService
    ) {}

    /**
     * Create a new record for a module.
     *
     * @param  array<string, mixed>  $data  Field values keyed by api_name
     *
     * @throws RuntimeException If record creation fails
     */
    public function createRecord(int $moduleId, array $data, ?int $createdBy = null): ModuleRecord
    {
        $module = ModuleModel::with(['blocks.fields.options'])->findOrFail($moduleId);

        if (! $module->is_active) {
            throw new RuntimeException('Cannot create records for inactive modules.');
        }

        DB::beginTransaction();

        try {
            // Validate and transform data
            $validatedData = $this->validateAndTransformData($module, $data);

            // Create domain entity
            $record = ModuleRecord::create(
                $moduleId,
                $validatedData,
                $createdBy ?? auth()->id()
            );

            // Persist using repository
            $savedRecord = $this->recordRepository->save($record);

            DB::commit();

            return $savedRecord;
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
    public function updateRecord(int $moduleId, int $recordId, array $data, ?int $updatedBy = null): ModuleRecord
    {
        DB::beginTransaction();

        try {
            // Load module for validation
            $module = ModuleModel::with(['blocks.fields.options'])->findOrFail($moduleId);

            if (! $module->is_active) {
                throw new RuntimeException('Cannot update records for inactive modules.');
            }

            // Get existing record
            $record = $this->recordRepository->findById($moduleId, $recordId);

            if (! $record) {
                throw new RuntimeException("Record not found with ID {$recordId}.");
            }

            // Validate and transform data, merging with existing
            $validatedData = $this->validateAndTransformData($module, $data, $record->data());

            // Update domain entity
            $record->updateData($validatedData, $updatedBy ?? auth()->id());

            // Persist using repository
            $updatedRecord = $this->recordRepository->save($record);

            DB::commit();

            return $updatedRecord;
        } catch (Exception $e) {
            DB::rollBack();
            throw new RuntimeException("Failed to update record: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Delete a record.
     *
     * @throws RuntimeException If record deletion fails
     */
    public function deleteRecord(int $moduleId, int $recordId): bool
    {
        DB::beginTransaction();

        try {
            $success = $this->recordRepository->delete($moduleId, $recordId);

            if (! $success) {
                throw new RuntimeException("Record not found with ID {$recordId}.");
            }

            DB::commit();

            return $success;
        } catch (Exception $e) {
            DB::rollBack();
            throw new RuntimeException("Failed to delete record: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Get a single record by ID.
     */
    public function getRecord(int $moduleId, int $recordId): ?ModuleRecord
    {
        return $this->recordRepository->findById($moduleId, $recordId);
    }

    /**
     * Get all records for a module with pagination.
     *
     * @param  array<string, mixed>  $filters  Array of field filters ['field_name' => ['operator' => 'value']]
     * @param  array<string, string>  $sort  Array of sort rules ['field_name' => 'asc|desc']
     * @return array{data: ModuleRecord[], total: int, per_page: int, current_page: int, last_page: int}
     */
    public function getRecords(
        int $moduleId,
        array $filters = [],
        array $sort = [],
        int $page = 1,
        int $perPage = 15
    ): array {
        return $this->recordRepository->findAll(
            $moduleId,
            $filters,
            $sort,
            $page,
            $perPage
        );
    }

    /**
     * Search records across multiple fields.
     *
     * @param  array<string>  $searchableFields  Field api_names to search
     * @return array{data: ModuleRecord[], total: int, per_page: int, current_page: int, last_page: int}
     */
    public function searchRecords(
        int $moduleId,
        string $searchTerm,
        array $searchableFields = [],
        int $page = 1,
        int $perPage = 15
    ): array {
        // Build filters for contains operator across multiple fields
        $filters = [];

        if (empty($searchableFields)) {
            // When no specific fields provided, we can't search all fields easily
            // This would require fetching all fields from the module first
            $module = ModuleModel::with(['blocks.fields'])->findOrFail($moduleId);
            $searchableFields = [];

            foreach ($module->blocks as $block) {
                foreach ($block->fields as $field) {
                    $searchableFields[] = $field->api_name;
                }
            }
        }

        // Use 'contains' operator for each searchable field
        // Note: Repository currently doesn't support OR conditions across fields
        // This is a simplified implementation that filters by the first field only
        if (! empty($searchableFields)) {
            $filters[$searchableFields[0]] = [
                'operator' => 'contains',
                'value' => $searchTerm,
            ];
        }

        return $this->recordRepository->findAll(
            $moduleId,
            $filters,
            ['created_at' => 'desc'],
            $page,
            $perPage
        );
    }

    /**
     * Get records with specific field value.
     *
     * @return array{data: ModuleRecord[], total: int, per_page: int, current_page: int, last_page: int}
     */
    public function getRecordsByField(
        int $moduleId,
        string $fieldApiName,
        mixed $value,
        int $page = 1,
        int $perPage = 15
    ): array {
        $filters = [
            $fieldApiName => [
                'operator' => 'equals',
                'value' => $value,
            ],
        ];

        return $this->recordRepository->findAll(
            $moduleId,
            $filters,
            ['created_at' => 'desc'],
            $page,
            $perPage
        );
    }

    /**
     * Get record count for a module.
     */
    public function getRecordCount(int $moduleId, array $filters = []): int
    {
        return $this->recordRepository->count($moduleId, $filters);
    }

    /**
     * Bulk create records.
     *
     * @param  array<array<string, mixed>>  $records  Array of record data
     * @return array<ModuleRecord>
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

                // Create domain entity
                $record = ModuleRecord::create(
                    $moduleId,
                    $validatedData,
                    $createdBy ?? auth()->id()
                );

                // Persist using repository
                $savedRecord = $this->recordRepository->save($record);
                $createdRecords[] = $savedRecord;
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
    public function bulkDeleteRecords(int $moduleId, array $recordIds): int
    {
        DB::beginTransaction();

        try {
            $deleted = $this->recordRepository->bulkDelete($moduleId, $recordIds);

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

            if (! $module->is_active) {
                throw new RuntimeException('Cannot update records for inactive modules.');
            }

            $updatedCount = 0;

            foreach ($recordIds as $recordId) {
                // Get existing record
                $record = $this->recordRepository->findById($moduleId, $recordId);

                if (! $record) {
                    continue; // Skip if record not found
                }

                // Merge new data with existing data
                $validatedData = $this->validateAndTransformData($module, $data, $record->data());

                // Update domain entity
                $record->updateData($validatedData, $updatedBy ?? auth()->id());

                // Persist using repository
                $this->recordRepository->save($record);
                $updatedCount++;
            }

            DB::commit();

            return $updatedCount;
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
        // Get all records (no pagination for export)
        $result = $this->recordRepository->findAll(
            $moduleId,
            [],
            ['created_at' => 'desc'],
            1,
            999999 // Large limit to get all records
        );

        return array_map(function (ModuleRecord $record) {
            return array_merge(
                $record->data(),
                [
                    'id' => $record->id(),
                    'created_at' => $record->createdAt()?->format('c'),
                    'updated_at' => $record->updatedAt()?->format('c'),
                ]
            );
        }, $result['data']);
    }

    /**
     * Get unique values for a specific field.
     *
     * @return array<mixed>
     */
    public function getUniqueFieldValues(int $moduleId, string $fieldApiName): array
    {
        // Get all records
        $result = $this->recordRepository->findAll(
            $moduleId,
            [],
            [],
            1,
            999999
        );

        $values = [];
        foreach ($result['data'] as $record) {
            $value = $record->getFieldValue($fieldApiName);
            if ($value !== null && ! in_array($value, $values, true)) {
                $values[] = $value;
            }
        }

        return $values;
    }

    /**
     * Check if a record exists.
     */
    public function recordExists(int $moduleId, int $recordId): bool
    {
        return $this->recordRepository->exists($moduleId, $recordId);
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

                    // Apply type conversion
                    $transformedValue = $this->convertFieldValue($field, $value);

                    // Store validated and transformed value
                    $validatedData[$apiName] = $transformedValue;
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

    /**
     * Convert field value to appropriate type based on field type.
     */
    private function convertFieldValue($field, mixed $value): mixed
    {
        // Return null as-is
        if ($value === null || $value === '') {
            return null;
        }

        return match ($field->type) {
            'number' => is_numeric($value) ? (int) $value : $value,
            'decimal', 'currency', 'percent' => is_numeric($value) ? (float) $value : $value,
            'checkbox', 'toggle' => (bool) $value,
            'multiselect' => is_array($value) ? $value : [$value],
            'date', 'datetime' => is_string($value) ? $value : (string) $value,
            default => $value,
        };
    }
}

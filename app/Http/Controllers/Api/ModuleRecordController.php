<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\Models\ModuleModel;
use App\Services\RecordService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

final class ModuleRecordController extends Controller
{
    public function __construct(
        private readonly RecordService $recordService
    ) {}

    /**
     * Get paginated records for a module.
     */
    public function index(Request $request, string $moduleApiName): JsonResponse
    {
        $module = ModuleModel::with(['blocks.fields'])->where('api_name', $moduleApiName)->firstOrFail();

        $perPage = (int) $request->query('per_page', 50);
        $page = (int) $request->query('page', 1);

        // Get valid field names for validation
        $validFields = $module->blocks->flatMap->fields->pluck('api_name')->toArray();

        // Transform filters from frontend format to repository format
        $filters = [];
        if ($filterData = $request->query('filters')) {
            if (is_string($filterData)) {
                $filterData = json_decode($filterData, true);
            }

            if (is_array($filterData)) {
                foreach ($filterData as $filter) {
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

                    // Map operators to repository format
                    $operatorMap = [
                        'gt' => 'greater_than',
                        'gte' => 'greater_than_or_equal',
                        'lt' => 'less_than',
                        'lte' => 'less_than_or_equal',
                    ];

                    $repoOperator = $operatorMap[$operator] ?? $operator;

                    $filters[$field] = [
                        'operator' => $repoOperator,
                        'value' => $value,
                    ];
                }
            }
        }

        // Transform sort from frontend format to repository format
        $sort = [];
        if ($sortData = $request->query('sort')) {
            if (is_string($sortData)) {
                $sortData = json_decode($sortData, true);
            }

            if (is_array($sortData)) {
                foreach ($sortData as $sortConfig) {
                    if (! isset($sortConfig['field'], $sortConfig['direction'])) {
                        continue;
                    }

                    $field = $sortConfig['field'];
                    $direction = mb_strtolower($sortConfig['direction']);

                    // Validate field and direction
                    if (! in_array($field, $validFields, true) && ! in_array($field, ['id', 'created_at', 'updated_at'], true)) {
                        continue;
                    }

                    if (! in_array($direction, ['asc', 'desc'], true)) {
                        continue;
                    }

                    $sort[$field] = $direction;
                }
            }
        }

        // Default sort if none provided
        if (empty($sort)) {
            $sort = ['created_at' => 'desc'];
        }

        // Use service to get records
        $result = $this->recordService->getRecords($module->id, $filters, $sort, $page, $perPage);

        return response()->json([
            'data' => array_map(fn ($record) => [
                'id' => $record->id(),
                'data' => $record->data(),
                'created_at' => $record->createdAt()?->format('c'),
                'updated_at' => $record->updatedAt()?->format('c'),
            ], $result['data']),
            'meta' => [
                'current_page' => $result['current_page'],
                'from' => ($result['current_page'] - 1) * $result['per_page'] + 1,
                'last_page' => $result['last_page'],
                'per_page' => $result['per_page'],
                'to' => min($result['current_page'] * $result['per_page'], $result['total']),
                'total' => $result['total'],
            ],
        ]);
    }

    /**
     * Get a single record by ID.
     */
    public function show(string $moduleApiName, int $id): JsonResponse
    {
        $module = ModuleModel::where('api_name', $moduleApiName)->firstOrFail();

        $record = $this->recordService->getRecord($module->id, $id);

        if (! $record) {
            return response()->json([
                'message' => 'Record not found',
            ], 404);
        }

        return response()->json([
            'data' => [
                'id' => $record->id(),
                'data' => $record->data(),
                'created_at' => $record->createdAt()?->format('c'),
                'updated_at' => $record->updatedAt()?->format('c'),
            ],
        ]);
    }

    /**
     * Create a new record.
     */
    public function store(Request $request, string $moduleApiName): JsonResponse
    {
        $module = ModuleModel::with(['blocks.fields.options'])->where('api_name', $moduleApiName)->firstOrFail();

        try {
            $record = $this->recordService->createRecord(
                $module->id,
                $request->all(),
                auth()->id()
            );

            return response()->json([
                'data' => [
                    'id' => $record->id(),
                    'data' => $record->data(),
                    'created_at' => $record->createdAt()?->format('c'),
                    'updated_at' => $record->updatedAt()?->format('c'),
                ],
                'message' => ucfirst($module->name).' created successfully',
            ], 201);
        } catch (RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Update an existing record.
     */
    public function update(Request $request, string $moduleApiName, int $id): JsonResponse
    {
        $module = ModuleModel::with(['blocks.fields.options'])->where('api_name', $moduleApiName)->firstOrFail();

        try {
            $record = $this->recordService->updateRecord(
                $module->id,
                $id,
                $request->all(),
                auth()->id()
            );

            return response()->json([
                'data' => [
                    'id' => $record->id(),
                    'data' => $record->data(),
                    'created_at' => $record->createdAt()?->format('c'),
                    'updated_at' => $record->updatedAt()?->format('c'),
                ],
                'message' => ucfirst($module->name).' updated successfully',
            ]);
        } catch (RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Delete a record.
     */
    public function destroy(string $moduleApiName, int $id): JsonResponse
    {
        $module = ModuleModel::where('api_name', $moduleApiName)->firstOrFail();

        try {
            // Handle related records if RelatedRecordsService exists
            if (class_exists(\App\Services\RelatedRecordsService::class)) {
                $relatedRecordsService = app(\App\Services\RelatedRecordsService::class);
                $relatedRecordsService->handleCascadeDelete($module->id, $id);
                $relatedRecordsService->cleanupOrphanedReferences($module->id, $id);
            }

            // Delete the record
            $this->recordService->deleteRecord($module->id, $id);

            return response()->json([
                'message' => ucfirst($module->name).' deleted successfully',
            ]);
        } catch (RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Bulk delete records.
     */
    public function bulkDelete(Request $request, string $moduleApiName): JsonResponse
    {
        $module = ModuleModel::where('api_name', $moduleApiName)->firstOrFail();

        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer'],
        ]);

        try {
            $count = $this->recordService->bulkDeleteRecords($module->id, $validated['ids']);

            return response()->json([
                'message' => "Successfully deleted {$count} record(s)",
                'count' => $count,
            ]);
        } catch (RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Bulk update records.
     */
    public function bulkUpdate(Request $request, string $moduleApiName): JsonResponse
    {
        $module = ModuleModel::where('api_name', $moduleApiName)->firstOrFail();

        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer'],
            'data' => ['required', 'array'],
        ]);

        try {
            $count = $this->recordService->bulkUpdateRecords(
                $module->id,
                $validated['ids'],
                $validated['data'],
                auth()->id()
            );

            return response()->json([
                'message' => "Successfully updated {$count} record(s)",
                'count' => $count,
            ]);
        } catch (RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}

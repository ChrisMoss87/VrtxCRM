<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\Models\ModuleRecordModel;
use App\Services\RelatedRecordsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

/**
 * Related Records API Controller
 *
 * Handles operations for managing related records between modules.
 */
final class RelatedRecordsController extends Controller
{
    public function __construct(
        private readonly RelatedRecordsService $relatedRecordsService,
    ) {}

    /**
     * Get all related records for a given record.
     *
     * GET /api/modules/{moduleApiName}/records/{id}/related
     */
    public function index(string $moduleApiName, int $id): JsonResponse
    {
        $record = ModuleRecordModel::whereHas('module', function ($query) use ($moduleApiName) {
            $query->where('api_name', $moduleApiName);
        })->with('module')->findOrFail($id);

        $relatedRecords = $this->relatedRecordsService->getRelatedRecords(
            $record->module_id,
            $record->id
        );

        return response()->json([
            'data' => $relatedRecords,
        ]);
    }

    /**
     * Link records via a relationship.
     *
     * POST /api/relationships/{relationshipId}/link
     *
     * Body:
     * {
     *   "source_record_id": 1,
     *   "target_record_ids": [2, 3, 4]
     * }
     */
    public function link(Request $request, int $relationshipId): JsonResponse
    {
        $validated = $request->validate([
            'source_record_id' => ['required', 'integer', 'exists:module_records,id'],
            'target_record_ids' => ['required', 'array'],
            'target_record_ids.*' => ['integer', 'exists:module_records,id'],
        ]);

        try {
            $this->relatedRecordsService->linkRecords(
                $relationshipId,
                $validated['source_record_id'],
                $validated['target_record_ids']
            );

            return response()->json([
                'message' => 'Records linked successfully',
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Unlink records from a relationship.
     *
     * POST /api/relationships/{relationshipId}/unlink
     *
     * Body:
     * {
     *   "source_record_id": 1,
     *   "target_record_ids": [2, 3]
     * }
     */
    public function unlink(Request $request, int $relationshipId): JsonResponse
    {
        $validated = $request->validate([
            'source_record_id' => ['required', 'integer', 'exists:module_records,id'],
            'target_record_ids' => ['required', 'array'],
            'target_record_ids.*' => ['integer', 'exists:module_records,id'],
        ]);

        try {
            $this->relatedRecordsService->unlinkRecords(
                $relationshipId,
                $validated['source_record_id'],
                $validated['target_record_ids']
            );

            return response()->json([
                'message' => 'Records unlinked successfully',
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get available records to link (for lookup field dropdowns).
     *
     * GET /api/relationships/{relationshipId}/available?search=...&limit=50
     */
    public function available(Request $request, int $relationshipId): JsonResponse
    {
        $relationship = app(\App\Domain\Modules\Repositories\ModuleRelationshipRepositoryInterface::class)
            ->findById($relationshipId);

        if (! $relationship) {
            return response()->json([
                'message' => 'Relationship not found',
            ], 404);
        }

        $search = $request->query('search');
        $limit = min((int) $request->query('limit', 50), 100);

        $query = ModuleRecordModel::where('module_id', $relationship->toModuleId());

        // Apply search if provided
        if ($search) {
            $query->where('data', 'like', "%{$search}%");
        }

        // Apply any filters from relationship settings
        $filters = $relationship->settings()->filters();
        if ($filters) {
            foreach ($filters as $field => $value) {
                $query->whereRaw("JSON_EXTRACT(data, '$.{$field}') = ?", [$value]);
            }
        }

        // Sort by configured field
        $sortField = $relationship->getSortField();
        $sortDirection = $relationship->getSortDirection();

        if ($sortField === 'created_at' || $sortField === 'updated_at') {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->orderByRaw("data->>'$.{$sortField}' {$sortDirection}");
        }

        $records = $query->limit($limit)->get();

        return response()->json([
            'data' => $records->map(function ($record) use ($relationship) {
                $displayField = $relationship->getDisplayField();

                return [
                    'id' => $record->id,
                    'label' => $record->data[$displayField] ?? "Record #{$record->id}",
                    'data' => $record->data,
                ];
            }),
        ]);
    }
}

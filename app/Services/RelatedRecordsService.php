<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\Modules\Repositories\ModuleRelationshipRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\ModuleRecordModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

/**
 * Related Records Service
 *
 * Handles operations related to record relationships:
 * - Cascade delete handling
 * - Orphan record cleanup
 * - Related record retrieval
 */
final class RelatedRecordsService
{
    public function __construct(
        private readonly ModuleRelationshipRepositoryInterface $relationshipRepository,
    ) {}

    /**
     * Handle cascade deletes when a record is deleted.
     * Deletes related records if cascade_delete is enabled on the relationship.
     */
    public function handleCascadeDelete(int $moduleId, int $recordId): void
    {
        // Find all relationships where this module is the "from" module
        $relationships = $this->relationshipRepository->findByFromModule($moduleId);

        foreach ($relationships as $relationship) {
            if (! $relationship->shouldCascadeDelete()) {
                continue;
            }

            // Get the related module ID
            $relatedModuleId = $relationship->toModuleId();

            // Find all records in the related module that reference this record
            $relatedRecords = ModuleRecordModel::where('module_id', $relatedModuleId)
                ->where(function ($query) use ($relationship, $recordId) {
                    $apiName = $relationship->apiName();

                    // For one-to-many: data->field_name = record_id
                    if ($relationship->isOneToMany()) {
                        $query->whereRaw("JSON_EXTRACT(data, '$.{$apiName}') = ?", [$recordId]);
                    }
                    // For many-to-many: data->field_name contains record_id in array
                    elseif ($relationship->isManyToMany()) {
                        $query->whereRaw("JSON_CONTAINS(data, ?, '$.{$apiName}')", [json_encode($recordId)]);
                    }
                })
                ->get();

            // Delete each related record
            foreach ($relatedRecords as $relatedRecord) {
                Log::info('Cascade deleting related record', [
                    'relationship' => $relationship->name(),
                    'record_id' => $relatedRecord->id,
                    'parent_record_id' => $recordId,
                ]);

                $relatedRecord->delete();
            }
        }
    }

    /**
     * Clean up orphaned references when a record is deleted.
     * Removes references from lookup fields in other records.
     */
    public function cleanupOrphanedReferences(int $moduleId, int $recordId): void
    {
        // Find all relationships where this module is the "to" module
        $relationships = $this->relationshipRepository->findByToModule($moduleId);

        foreach ($relationships as $relationship) {
            if ($relationship->shouldCascadeDelete()) {
                // Already handled by cascade delete
                continue;
            }

            $sourceModuleId = $relationship->fromModuleId();
            $apiName = $relationship->apiName();

            // For one-to-many: Set field to null
            if ($relationship->isOneToMany()) {
                DB::table('module_records')
                    ->where('module_id', $sourceModuleId)
                    ->whereRaw("JSON_EXTRACT(data, '$.{$apiName}') = ?", [$recordId])
                    ->update([
                        'data' => DB::raw("JSON_SET(data, '$.{$apiName}', NULL)"),
                        'updated_at' => now(),
                    ]);

                Log::info('Cleaned up orphaned one-to-many reference', [
                    'relationship' => $relationship->name(),
                    'deleted_record_id' => $recordId,
                ]);
            }
            // For many-to-many: Remove from array
            elseif ($relationship->isManyToMany()) {
                $records = ModuleRecordModel::where('module_id', $sourceModuleId)
                    ->whereRaw("JSON_CONTAINS(data, ?, '$.{$apiName}')", [json_encode($recordId)])
                    ->get();

                foreach ($records as $record) {
                    $data = $record->data;
                    $currentValues = $data[$apiName] ?? [];

                    if (is_array($currentValues)) {
                        // Remove the deleted record ID from the array
                        $data[$apiName] = array_values(array_filter($currentValues, fn ($id) => $id !== $recordId));
                        $record->update(['data' => $data]);

                        Log::info('Cleaned up orphaned many-to-many reference', [
                            'relationship' => $relationship->name(),
                            'record_id' => $record->id,
                            'deleted_record_id' => $recordId,
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Get all related records for a given record.
     *
     * @return array<string, array<ModuleRecordModel>>
     */
    public function getRelatedRecords(int $moduleId, int $recordId): array
    {
        $relationships = $this->relationshipRepository->findByFromModule($moduleId);
        $relatedRecords = [];

        foreach ($relationships as $relationship) {
            $relatedModuleId = $relationship->toModuleId();
            $apiName = $relationship->apiName();

            $query = ModuleRecordModel::where('module_id', $relatedModuleId);

            // For one-to-many: data->field_name = record_id
            if ($relationship->isOneToMany()) {
                $query->whereRaw("JSON_EXTRACT(data, '$.{$apiName}') = ?", [$recordId]);
            }
            // For many-to-many: data->field_name contains record_id in array
            elseif ($relationship->isManyToMany()) {
                $query->whereRaw("JSON_CONTAINS(data, ?, '$.{$apiName}')", [json_encode($recordId)]);
            }

            $relatedRecords[$relationship->name()] = $query->get()->toArray();
        }

        return $relatedRecords;
    }

    /**
     * Link a record to related records via a relationship.
     */
    public function linkRecords(int $relationshipId, int $sourceRecordId, array $targetRecordIds): void
    {
        $relationship = $this->relationshipRepository->findById($relationshipId);

        if (! $relationship) {
            throw new InvalidArgumentException("Relationship {$relationshipId} not found");
        }

        // Get the source record
        $sourceRecord = ModuleRecordModel::where('module_id', $relationship->fromModuleId())
            ->where('id', $sourceRecordId)
            ->firstOrFail();

        $data = $sourceRecord->data;
        $apiName = $relationship->apiName();

        // For one-to-many: Set single ID
        if ($relationship->isOneToMany()) {
            if (count($targetRecordIds) > 1) {
                throw new InvalidArgumentException('One-to-many relationship can only link to one record');
            }

            $data[$apiName] = $targetRecordIds[0] ?? null;
        }
        // For many-to-many: Set array of IDs
        elseif ($relationship->isManyToMany()) {
            $data[$apiName] = $targetRecordIds;
        }

        $sourceRecord->update(['data' => $data]);

        Log::info('Linked records', [
            'relationship' => $relationship->name(),
            'source_record_id' => $sourceRecordId,
            'target_record_ids' => $targetRecordIds,
        ]);
    }

    /**
     * Unlink a record from related records.
     */
    public function unlinkRecords(int $relationshipId, int $sourceRecordId, array $targetRecordIds): void
    {
        $relationship = $this->relationshipRepository->findById($relationshipId);

        if (! $relationship) {
            throw new InvalidArgumentException("Relationship {$relationshipId} not found");
        }

        $sourceRecord = ModuleRecordModel::where('module_id', $relationship->fromModuleId())
            ->where('id', $sourceRecordId)
            ->firstOrFail();

        $data = $sourceRecord->data;
        $apiName = $relationship->apiName();

        // For one-to-many: Set to null
        if ($relationship->isOneToMany()) {
            $data[$apiName] = null;
        }
        // For many-to-many: Remove specific IDs from array
        elseif ($relationship->isManyToMany()) {
            $currentValues = $data[$apiName] ?? [];
            if (is_array($currentValues)) {
                $data[$apiName] = array_values(array_filter($currentValues, fn ($id) => ! in_array($id, $targetRecordIds)));
            }
        }

        $sourceRecord->update(['data' => $data]);

        Log::info('Unlinked records', [
            'relationship' => $relationship->name(),
            'source_record_id' => $sourceRecordId,
            'target_record_ids' => $targetRecordIds,
        ]);
    }
}

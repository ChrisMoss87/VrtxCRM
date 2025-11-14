<?php

declare(strict_types=1);

namespace App\Services;

use App\Infrastructure\Persistence\Eloquent\Models\BlockModel;
use Exception;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class BlockService
{
    /**
     * Create a new block.
     *
     * @param  array{type: string, label: string, order?: int, settings?: array}  $data
     *
     * @throws RuntimeException If block creation fails
     */
    public function createBlock(int $moduleId, array $data): BlockModel
    {
        DB::beginTransaction();

        try {
            $block = BlockModel::create([
                'module_id' => $moduleId,
                'type' => $data['type'],
                'label' => $data['label'],
                'order' => $data['order'] ?? 0,
                'settings' => $data['settings'] ?? [],
            ]);

            DB::commit();

            return $block->fresh(['fields.options']);
        } catch (Exception $e) {
            DB::rollBack();
            throw new RuntimeException("Failed to create block: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Update an existing block.
     *
     * @throws RuntimeException If block update fails
     */
    public function updateBlock(int $blockId, array $data): BlockModel
    {
        DB::beginTransaction();

        try {
            $block = BlockModel::findOrFail($blockId);

            // Check if module is system module
            if ($block->module->is_system) {
                throw new RuntimeException('Cannot modify blocks in system modules.');
            }

            $block->update([
                'type' => $data['type'] ?? $block->type,
                'label' => $data['label'] ?? $block->label,
                'order' => $data['order'] ?? $block->order,
                'settings' => $data['settings'] ?? $block->settings,
            ]);

            DB::commit();

            return $block->fresh(['fields.options']);
        } catch (Exception $e) {
            DB::rollBack();
            throw new RuntimeException("Failed to update block: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Delete a block.
     *
     * @throws RuntimeException If block deletion fails
     */
    public function deleteBlock(int $blockId): void
    {
        DB::beginTransaction();

        try {
            $block = BlockModel::findOrFail($blockId);

            if ($block->module->is_system) {
                throw new RuntimeException('Cannot delete blocks from system modules.');
            }

            // Delete will cascade to fields and field options
            $block->delete();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw new RuntimeException("Failed to delete block: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Reorder blocks within a module.
     *
     * @param  array<int, int>  $order  Array of block_id => order
     */
    public function reorderBlocks(int $moduleId, array $order): void
    {
        DB::beginTransaction();

        try {
            // Verify all blocks belong to this module
            $blockIds = array_keys($order);
            $validBlocks = BlockModel::where('module_id', $moduleId)
                ->whereIn('id', $blockIds)
                ->count();

            if ($validBlocks !== count($blockIds)) {
                throw new RuntimeException('Invalid block IDs provided.');
            }

            foreach ($order as $blockId => $position) {
                BlockModel::where('id', $blockId)->update(['order' => $position]);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw new RuntimeException("Failed to reorder blocks: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Get block with fields and options.
     */
    public function getBlock(int $blockId): BlockModel
    {
        return BlockModel::with(['fields.options', 'module'])
            ->findOrFail($blockId);
    }

    /**
     * Get all blocks for a module.
     */
    public function getBlocksForModule(int $moduleId): iterable
    {
        return BlockModel::where('module_id', $moduleId)
            ->with(['fields.options'])
            ->orderBy('order')
            ->orderBy('label')
            ->get();
    }
}

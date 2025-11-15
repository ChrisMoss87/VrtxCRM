<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\Modules\Entities\Block;
use App\Domain\Modules\Repositories\BlockRepositoryInterface;
use App\Domain\Modules\Repositories\ModuleRepositoryInterface;
use App\Domain\Modules\ValueObjects\BlockType;
use RuntimeException;

final class BlockService
{
    public function __construct(
        private readonly BlockRepositoryInterface $blockRepository,
        private readonly ModuleRepositoryInterface $moduleRepository
    ) {}

    /**
     * Create a new block.
     *
     * @param  array{type: string, label: string, order?: int, settings?: array}  $data
     *
     * @throws RuntimeException If block creation fails or module is a system module
     */
    public function createBlock(int $moduleId, array $data): Block
    {
        // Check if module exists and is not a system module
        $module = $this->moduleRepository->findById($moduleId);
        if (! $module) {
            throw new RuntimeException('Module not found.');
        }

        if ($module->isSystem()) {
            throw new RuntimeException('Cannot modify blocks in system modules.');
        }

        $block = Block::create(
            moduleId: $moduleId,
            label: $data['label'],
            type: BlockType::from($data['type']),
            order: $data['order'] ?? 0,
            settings: $data['settings'] ?? []
        );

        return $this->blockRepository->save($block);
    }

    /**
     * Update an existing block.
     *
     * @throws RuntimeException If block update fails or module is a system module
     */
    public function updateBlock(int $blockId, array $data): Block
    {
        $block = $this->blockRepository->findById($blockId);
        if (! $block) {
            throw new RuntimeException('Block not found.');
        }

        // Check if module is system module
        $module = $this->moduleRepository->findById($block->moduleId());
        if ($module && $module->isSystem()) {
            throw new RuntimeException('Cannot modify blocks in system modules.');
        }

        // Update block details
        $block->updateDetails(
            label: $data['label'] ?? $block->label(),
            type: isset($data['type']) ? BlockType::from($data['type']) : $block->type(),
            settings: $data['settings'] ?? $block->settings()
        );

        if (isset($data['order'])) {
            $block->updateOrder($data['order']);
        }

        return $this->blockRepository->save($block);
    }

    /**
     * Delete a block.
     *
     * @throws RuntimeException If block deletion fails or module is a system module
     */
    public function deleteBlock(int $blockId): void
    {
        $block = $this->blockRepository->findById($blockId);
        if (! $block) {
            throw new RuntimeException('Block not found.');
        }

        // Check if module is system module
        $module = $this->moduleRepository->findById($block->moduleId());
        if ($module && $module->isSystem()) {
            throw new RuntimeException('Cannot delete blocks from system modules.');
        }

        $this->blockRepository->delete($blockId);
    }

    /**
     * Reorder blocks within a module.
     *
     * @param  array<int, int>  $order  Array of block_id => order
     *
     * @throws RuntimeException If reordering fails
     */
    public function reorderBlocks(int $moduleId, array $order): void
    {
        // Verify all blocks belong to this module
        $blocks = $this->blockRepository->findByModuleId($moduleId);
        $blockIds = array_map(fn (Block $block) => $block->id(), $blocks);

        foreach (array_keys($order) as $blockId) {
            if (! in_array($blockId, $blockIds, true)) {
                throw new RuntimeException('Invalid block IDs provided.');
            }
        }

        // Update each block's order
        foreach ($order as $blockId => $position) {
            $block = $this->blockRepository->findById($blockId);
            if ($block) {
                $block->updateOrder($position);
                $this->blockRepository->save($block);
            }
        }
    }

    /**
     * Get block with fields.
     */
    public function getBlock(int $blockId): Block
    {
        $block = $this->blockRepository->findById($blockId);
        if (! $block) {
            throw new RuntimeException('Block not found.');
        }

        return $block;
    }

    /**
     * Get all blocks for a module.
     *
     * @return Block[]
     */
    public function getBlocksForModule(int $moduleId): array
    {
        return $this->blockRepository->findByModuleId($moduleId);
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Services\BlockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use RuntimeException;

final class BlockManagementController extends Controller
{
    public function __construct(
        private readonly BlockService $blockService
    ) {}

    /**
     * Get all blocks for a module.
     */
    public function index(int $moduleId): JsonResponse
    {
        try {
            $blocks = $this->blockService->getBlocksForModule($moduleId);

            return response()->json([
                'blocks' => array_map(fn ($block) => [
                    'id' => $block->id(),
                    'module_id' => $block->moduleId(),
                    'type' => $block->type()->value,
                    'label' => $block->label(),
                    'order' => $block->order(),
                    'settings' => $block->settings(),
                    'fields' => array_map(fn ($field) => [
                        'id' => $field->id(),
                        'block_id' => $field->blockId(),
                        'type' => $field->type()->value,
                        'label' => $field->label(),
                        'api_name' => $field->apiName(),
                        'description' => $field->description(),
                        'help_text' => $field->helpText(),
                        'is_required' => $field->isRequired(),
                        'is_unique' => $field->isUnique(),
                        'is_searchable' => $field->isSearchable(),
                        'order' => $field->order(),
                        'default_value' => $field->defaultValue(),
                        'validation_rules' => $field->validationRules()->jsonSerialize(),
                        'settings' => $field->settings()->jsonSerialize(),
                        'width' => $field->width(),
                        'options' => $field->options(),
                    ], $block->fields()),
                ], $blocks),
            ]);
        } catch (RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Create a new block.
     */
    public function store(int $moduleId, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'string', Rule::in(['section', 'tab', 'accordion'])],
            'label' => ['required', 'string', 'max:255'],
            'order' => ['nullable', 'integer', 'min:0'],
            'settings' => ['nullable', 'array'],
        ]);

        try {
            $block = $this->blockService->createBlock(
                moduleId: $moduleId,
                data: $validated
            );

            return response()->json([
                'block' => [
                    'id' => $block->id(),
                    'module_id' => $block->moduleId(),
                    'type' => $block->type()->value,
                    'label' => $block->label(),
                    'order' => $block->order(),
                    'settings' => $block->settings(),
                ],
            ], 201);
        } catch (RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getMessage() === 'Cannot modify blocks in system modules.' ? 403 : 422);
        }
    }

    /**
     * Update a block.
     */
    public function update(int $moduleId, int $id, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['sometimes', 'string', Rule::in(['section', 'tab', 'accordion'])],
            'label' => ['sometimes', 'string', 'max:255'],
            'order' => ['sometimes', 'integer', 'min:0'],
            'settings' => ['sometimes', 'array'],
        ]);

        try {
            $block = $this->blockService->updateBlock($id, $validated);

            return response()->json([
                'block' => [
                    'id' => $block->id(),
                    'module_id' => $block->moduleId(),
                    'type' => $block->type()->value,
                    'label' => $block->label(),
                    'order' => $block->order(),
                    'settings' => $block->settings(),
                ],
            ]);
        } catch (RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Delete a block.
     */
    public function destroy(int $moduleId, int $id): JsonResponse
    {
        try {
            $this->blockService->deleteBlock($id);

            return response()->json(['message' => 'Block deleted successfully.']);
        } catch (RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Reorder blocks within a module.
     */
    public function reorder(int $moduleId, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['required', 'integer'],
        ]);

        try {
            $this->blockService->reorderBlocks($moduleId, $validated['order']);

            return response()->json(['message' => 'Blocks reordered successfully.']);
        } catch (RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\Models\BlockModel;
use App\Infrastructure\Persistence\Eloquent\Models\ModuleModel;
use App\Services\BlockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
        $module = ModuleModel::findOrFail($moduleId);

        $blocks = BlockModel::where('module_id', $moduleId)
            ->with(['fields.options'])
            ->orderBy('order')
            ->orderBy('label')
            ->get();

        return response()->json(['blocks' => $blocks]);
    }

    /**
     * Create a new block.
     */
    public function store(int $moduleId, Request $request): JsonResponse
    {
        $module = ModuleModel::findOrFail($moduleId);

        if ($module->is_system) {
            return response()->json([
                'error' => 'Cannot modify system modules.',
            ], 403);
        }

        $validated = $request->validate([
            'type' => ['required', 'string', Rule::in(['section', 'tab', 'accordion'])],
            'label' => ['required', 'string', 'max:255'],
            'order' => ['nullable', 'integer', 'min:0'],
            'settings' => ['nullable', 'array'],
        ]);

        $block = $this->blockService->createBlock(
            moduleId: $moduleId,
            data: $validated
        );

        return response()->json(['block' => $block], 201);
    }

    /**
     * Update a block.
     */
    public function update(int $moduleId, int $id, Request $request): JsonResponse
    {
        $block = BlockModel::where('module_id', $moduleId)->findOrFail($id);

        if ($block->module->is_system) {
            return response()->json([
                'error' => 'Cannot modify system modules.',
            ], 403);
        }

        $validated = $request->validate([
            'type' => ['sometimes', 'string', Rule::in(['section', 'tab', 'accordion'])],
            'label' => ['sometimes', 'string', 'max:255'],
            'order' => ['sometimes', 'integer', 'min:0'],
            'settings' => ['sometimes', 'array'],
        ]);

        $updated = $this->blockService->updateBlock($id, $validated);

        return response()->json(['block' => $updated]);
    }

    /**
     * Delete a block.
     */
    public function destroy(int $moduleId, int $id): JsonResponse
    {
        $block = BlockModel::where('module_id', $moduleId)->findOrFail($id);

        if ($block->module->is_system) {
            return response()->json([
                'error' => 'Cannot modify system modules.',
            ], 403);
        }

        $this->blockService->deleteBlock($id);

        return response()->json(['message' => 'Block deleted successfully.']);
    }

    /**
     * Reorder blocks within a module.
     */
    public function reorder(int $moduleId, Request $request): JsonResponse
    {
        $module = ModuleModel::findOrFail($moduleId);

        if ($module->is_system) {
            return response()->json([
                'error' => 'Cannot modify system modules.',
            ], 403);
        }

        $validated = $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['required', 'integer'],
        ]);

        $this->blockService->reorderBlocks($moduleId, $validated['order']);

        return response()->json(['message' => 'Blocks reordered successfully.']);
    }
}

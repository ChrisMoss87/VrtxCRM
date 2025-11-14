<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\Models\BlockModel;
use App\Infrastructure\Persistence\Eloquent\Models\FieldModel;
use App\Services\FieldService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class FieldManagementController extends Controller
{
    public function __construct(
        private readonly FieldService $fieldService
    ) {}

    /**
     * Get all fields for a block.
     */
    public function index(int $blockId): JsonResponse
    {
        $block = BlockModel::findOrFail($blockId);

        $fields = FieldModel::where('block_id', $blockId)
            ->with('options')
            ->orderBy('order')
            ->orderBy('label')
            ->get();

        return response()->json(['fields' => $fields]);
    }

    /**
     * Create a new field.
     */
    public function store(int $blockId, Request $request): JsonResponse
    {
        $block = BlockModel::findOrFail($blockId);

        if ($block->module->is_system) {
            return response()->json([
                'error' => 'Cannot modify system modules.',
            ], 403);
        }

        $validated = $request->validate([
            'type' => ['required', 'string', Rule::in(FieldService::FIELD_TYPES)],
            'label' => ['required', 'string', 'max:255'],
            'api_name' => ['nullable', 'string', 'max:255', 'regex:/^[a-z][a-z0-9_]*$/'],
            'description' => ['nullable', 'string'],
            'help_text' => ['nullable', 'string'],
            'is_required' => ['nullable', 'boolean'],
            'is_unique' => ['nullable', 'boolean'],
            'is_searchable' => ['nullable', 'boolean'],
            'order' => ['nullable', 'integer', 'min:0'],
            'default_value' => ['nullable', 'string'],
            'validation_rules' => ['nullable', 'array'],
            'settings' => ['nullable', 'array'],
            'width' => ['nullable', 'integer', Rule::in([25, 33, 50, 66, 75, 100])],
            'options' => ['nullable', 'array'],
            'options.*.label' => ['required', 'string'],
            'options.*.value' => ['required', 'string'],
            'options.*.color' => ['nullable', 'string'],
            'options.*.order' => ['nullable', 'integer'],
            'options.*.is_default' => ['nullable', 'boolean'],
        ]);

        $field = $this->fieldService->createField([
            ...$validated,
            'block_id' => $blockId,
        ]);

        return response()->json(['field' => $field], 201);
    }

    /**
     * Update a field.
     */
    public function update(int $blockId, int $id, Request $request): JsonResponse
    {
        $field = FieldModel::where('block_id', $blockId)->findOrFail($id);

        if ($field->block->module->is_system) {
            return response()->json([
                'error' => 'Cannot modify system modules.',
            ], 403);
        }

        $validated = $request->validate([
            'type' => ['sometimes', 'string', Rule::in(FieldService::FIELD_TYPES)],
            'label' => ['sometimes', 'string', 'max:255'],
            'api_name' => ['sometimes', 'string', 'max:255', 'regex:/^[a-z][a-z0-9_]*$/'],
            'description' => ['nullable', 'string'],
            'help_text' => ['nullable', 'string'],
            'is_required' => ['sometimes', 'boolean'],
            'is_unique' => ['sometimes', 'boolean'],
            'is_searchable' => ['sometimes', 'boolean'],
            'order' => ['sometimes', 'integer', 'min:0'],
            'default_value' => ['nullable', 'string'],
            'validation_rules' => ['sometimes', 'array'],
            'settings' => ['sometimes', 'array'],
            'width' => ['sometimes', 'integer', Rule::in([25, 33, 50, 66, 75, 100])],
        ]);

        $updated = $this->fieldService->updateField($id, $validated);

        return response()->json(['field' => $updated]);
    }

    /**
     * Delete a field.
     */
    public function destroy(int $blockId, int $id): JsonResponse
    {
        $field = FieldModel::where('block_id', $blockId)->findOrFail($id);

        if ($field->block->module->is_system) {
            return response()->json([
                'error' => 'Cannot modify system modules.',
            ], 403);
        }

        $this->fieldService->deleteField($id);

        return response()->json(['message' => 'Field deleted successfully.']);
    }

    /**
     * Reorder fields within a block.
     */
    public function reorder(int $blockId, Request $request): JsonResponse
    {
        $block = BlockModel::findOrFail($blockId);

        if ($block->module->is_system) {
            return response()->json([
                'error' => 'Cannot modify system modules.',
            ], 403);
        }

        $validated = $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['required', 'integer'],
        ]);

        $this->fieldService->reorderFields($blockId, $validated['order']);

        return response()->json(['message' => 'Fields reordered successfully.']);
    }
}

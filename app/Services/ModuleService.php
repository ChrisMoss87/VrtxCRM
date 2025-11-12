<?php

declare(strict_types=1);

namespace App\Services;

use App\Infrastructure\Persistence\Eloquent\Models\BlockModel;
use App\Infrastructure\Persistence\Eloquent\Models\FieldModel;
use App\Infrastructure\Persistence\Eloquent\Models\ModuleModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

final class ModuleService
{
    /**
     * Create a new module with blocks and fields.
     *
     * @param  array{name: string, singular_name: string, api_name?: string, icon?: string, description?: string, is_active?: bool, blocks?: array}  $data
     * @throws RuntimeException If module creation fails
     */
    public function createModule(array $data): ModuleModel
    {
        // Validate module name uniqueness
        $apiName = $data['api_name'] ?? Str::snake($data['name']);
        $this->validateModuleApiName($apiName);

        DB::beginTransaction();

        try {
            // Create module
            $module = ModuleModel::create([
                'name' => $data['name'],
                'singular_name' => $data['singular_name'],
                'api_name' => $apiName,
                'icon' => $data['icon'] ?? 'database',
                'description' => $data['description'] ?? null,
                'is_active' => $data['is_active'] ?? true,
                'is_system' => $data['is_system'] ?? false,
                'order' => $data['order'] ?? 0,
                'settings' => $data['settings'] ?? [],
            ]);

            // Create blocks if provided
            if (! empty($data['blocks'])) {
                foreach ($data['blocks'] as $blockData) {
                    $this->createBlock($module->id, $blockData, allowSystem: true);
                }
            }

            DB::commit();

            return $module->fresh(['blocks.fields']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw new RuntimeException("Failed to create module: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Update an existing module.
     *
     * @throws RuntimeException If module update fails
     */
    public function updateModule(int $moduleId, array $data): ModuleModel
    {
        DB::beginTransaction();

        try {
            $module = ModuleModel::findOrFail($moduleId);

            // Prevent editing system modules
            if ($module->is_system) {
                throw new RuntimeException('Cannot edit system modules.');
            }

            // Validate api_name if being changed
            if (isset($data['api_name']) && $data['api_name'] !== $module->api_name) {
                $this->validateModuleApiName($data['api_name'], $moduleId);
            }

            $module->update([
                'name' => $data['name'] ?? $module->name,
                'singular_name' => $data['singular_name'] ?? $module->singular_name,
                'api_name' => $data['api_name'] ?? $module->api_name,
                'icon' => $data['icon'] ?? $module->icon,
                'description' => $data['description'] ?? $module->description,
                'is_active' => $data['is_active'] ?? $module->is_active,
                'order' => $data['order'] ?? $module->order,
                'settings' => $data['settings'] ?? $module->settings,
            ]);

            DB::commit();

            return $module->fresh(['blocks.fields']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw new RuntimeException("Failed to update module: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Delete a module and all associated data.
     *
     * @throws RuntimeException If module deletion fails
     */
    public function deleteModule(int $moduleId): void
    {
        DB::beginTransaction();

        try {
            $module = ModuleModel::findOrFail($moduleId);

            // Prevent deleting system modules
            if ($module->is_system) {
                throw new RuntimeException('Cannot delete system modules.');
            }

            // Check if module has records
            $recordCount = $module->records()->count();
            if ($recordCount > 0) {
                throw new RuntimeException("Cannot delete module with {$recordCount} existing records. Delete records first.");
            }

            // Delete will cascade to blocks, fields, and field options
            $module->delete();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new RuntimeException("Failed to delete module: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Create a block within a module.
     *
     * @param  array{type: string, label: string, order?: int, settings?: array, fields?: array}  $data
     * @param  bool  $allowSystem  Allow creating blocks in system modules (used during initial module creation)
     * @throws RuntimeException If block creation fails
     */
    public function createBlock(int $moduleId, array $data, bool $allowSystem = false): BlockModel
    {
        // Validate module exists
        $module = ModuleModel::findOrFail($moduleId);

        if ($module->is_system && ! $allowSystem) {
            throw new RuntimeException('Cannot modify system modules.');
        }

        DB::beginTransaction();

        try {
            $block = BlockModel::create([
                'module_id' => $moduleId,
                'type' => $data['type'],
                'label' => $data['label'],
                'order' => $data['order'] ?? 0,
                'settings' => $data['settings'] ?? [],
            ]);

            // Create fields if provided
            if (! empty($data['fields'])) {
                foreach ($data['fields'] as $fieldData) {
                    $this->createFieldInBlock($block->id, $fieldData);
                }
            }

            DB::commit();

            return $block->fresh(['fields']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw new RuntimeException("Failed to create block: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Update a block.
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
                throw new RuntimeException('Cannot modify system module blocks.');
            }

            $block->update([
                'type' => $data['type'] ?? $block->type,
                'label' => $data['label'] ?? $block->label,
                'order' => $data['order'] ?? $block->order,
                'settings' => $data['settings'] ?? $block->settings,
            ]);

            DB::commit();

            return $block->fresh(['fields']);
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
            DB::rollBack();
            throw new RuntimeException("Failed to delete block: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Create a field within a block (internal helper).
     */
    private function createFieldInBlock(int $blockId, array $data): FieldModel
    {
        // Validate field api_name uniqueness within block
        $apiName = $data['api_name'] ?? Str::snake($data['label']);
        $this->validateFieldApiName($blockId, $apiName);

        return FieldModel::create([
            'block_id' => $blockId,
            'type' => $data['type'],
            'api_name' => $apiName,
            'label' => $data['label'],
            'description' => $data['description'] ?? null,
            'help_text' => $data['help_text'] ?? null,
            'is_required' => $data['is_required'] ?? false,
            'is_unique' => $data['is_unique'] ?? false,
            'is_searchable' => $data['is_searchable'] ?? false,
            'order' => $data['order'] ?? 0,
            'default_value' => $data['default_value'] ?? null,
            'validation_rules' => $data['validation_rules'] ?? [],
            'settings' => $data['settings'] ?? [],
            'width' => $data['width'] ?? 100,
        ]);
    }

    /**
     * Get all active modules with their structure.
     */
    public function getActiveModules(): iterable
    {
        return ModuleModel::where('is_active', true)
            ->with(['blocks.fields.options'])
            ->orderBy('order')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get a single module with full structure.
     */
    public function getModule(int $moduleId): ModuleModel
    {
        return ModuleModel::with(['blocks.fields.options'])
            ->findOrFail($moduleId);
    }

    /**
     * Get module by API name.
     */
    public function getModuleByApiName(string $apiName): ?ModuleModel
    {
        return ModuleModel::with(['blocks.fields.options'])
            ->where('api_name', $apiName)
            ->first();
    }

    /**
     * Toggle module active status.
     */
    public function toggleModuleStatus(int $moduleId): ModuleModel
    {
        $module = ModuleModel::findOrFail($moduleId);

        if ($module->is_system) {
            throw new RuntimeException('Cannot deactivate system modules.');
        }

        $module->update(['is_active' => ! $module->is_active]);

        return $module;
    }

    /**
     * Reorder modules.
     *
     * @param  array<int, int>  $order  Array of module_id => order
     */
    public function reorderModules(array $order): void
    {
        DB::beginTransaction();

        try {
            foreach ($order as $moduleId => $position) {
                ModuleModel::where('id', $moduleId)->update(['order' => $position]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new RuntimeException("Failed to reorder modules: {$e->getMessage()}", 0, $e);
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
        } catch (\Exception $e) {
            DB::rollBack();
            throw new RuntimeException("Failed to reorder blocks: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Validate module api_name is unique.
     *
     * @throws RuntimeException If api_name is taken
     */
    private function validateModuleApiName(string $apiName, ?int $excludeModuleId = null): void
    {
        $query = ModuleModel::where('api_name', $apiName);

        if ($excludeModuleId) {
            $query->where('id', '!=', $excludeModuleId);
        }

        if ($query->exists()) {
            throw new RuntimeException("Module with api_name '{$apiName}' already exists.");
        }
    }

    /**
     * Validate field api_name is unique within block.
     *
     * @throws RuntimeException If api_name is taken
     */
    private function validateFieldApiName(int $blockId, string $apiName, ?int $excludeFieldId = null): void
    {
        $query = FieldModel::where('block_id', $blockId)
            ->where('api_name', $apiName);

        if ($excludeFieldId) {
            $query->where('id', '!=', $excludeFieldId);
        }

        if ($query->exists()) {
            throw new RuntimeException("Field with api_name '{$apiName}' already exists in this block.");
        }
    }

    /**
     * Get module statistics.
     */
    public function getModuleStats(int $moduleId): array
    {
        $module = ModuleModel::findOrFail($moduleId);

        return [
            'name' => $module->name,
            'api_name' => $module->api_name,
            'total_blocks' => $module->blocks()->count(),
            'total_fields' => $module->fields()->count(),
            'total_records' => $module->records()->count(),
            'is_active' => $module->is_active,
            'is_system' => $module->is_system,
            'created_at' => $module->created_at,
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\Models\ModuleModel;
use Illuminate\Http\JsonResponse;

final class ModuleController extends Controller
{
    /**
     * Get all modules for the current tenant.
     * Returns modules with their blocks and fields for UI rendering.
     */
    public function index(): JsonResponse
    {
        $modules = ModuleModel::with(['blocks.fields.options'])
            ->orderBy('name')
            ->get()
            ->map(fn (ModuleModel $module) => [
                'id' => $module->id,
                'name' => $module->name,
                'api_name' => $module->api_name,
                'icon' => $module->icon,
                'is_system' => $module->is_system,
                'settings' => $module->settings,
                'created_at' => $module->created_at?->toISOString(),
            ]);

        return response()->json([
            'data' => $modules,
        ]);
    }

    /**
     * Get a single module with full definition (blocks, fields, options).
     * Used for form rendering and record detail views.
     */
    public function show(string $apiName): JsonResponse
    {
        $module = ModuleModel::where('api_name', $apiName)
            ->with(['blocks.fields.options'])
            ->firstOrFail();

        return response()->json([
            'data' => [
                'id' => $module->id,
                'name' => $module->name,
                'api_name' => $module->api_name,
                'icon' => $module->icon,
                'is_system' => $module->is_system,
                'settings' => $module->settings,
                'blocks' => $module->blocks->map(fn ($block) => [
                    'id' => $block->id,
                    'name' => $block->name,
                    'type' => $block->type,
                    'settings' => $block->settings,
                    'order' => $block->order,
                    'fields' => $block->fields->map(fn ($field) => [
                        'id' => $field->id,
                        'label' => $field->label,
                        'api_name' => $field->api_name,
                        'type' => $field->type,
                        'is_required' => $field->is_required,
                        'is_unique' => $field->is_unique,
                        'settings' => $field->settings,
                        'validation_rules' => $field->validation_rules,
                        'default_value' => $field->default_value,
                        'help_text' => $field->help_text,
                        'order' => $field->order,
                        'options' => $field->options->map(fn ($option) => [
                            'id' => $option->id,
                            'label' => $option->label,
                            'value' => $option->value,
                            'color' => $option->color,
                            'order' => $option->order,
                        ]),
                    ]),
                ]),
                'created_at' => $module->created_at?->toISOString(),
                'updated_at' => $module->updated_at?->toISOString(),
            ],
        ]);
    }
}

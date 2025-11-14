<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\Models\ModuleModel;
use App\Services\FieldService;
use App\Services\ModuleService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class ModuleBuilderController extends Controller
{
    public function __construct(
        private readonly ModuleService $moduleService,
        private readonly FieldService $fieldService,
    ) {}

    /**
     * Display a listing of modules.
     */
    public function index(Request $request): Response
    {
        // TODO: Add authorization when policies are implemented
        // $this->authorize('viewAny', ModuleModel::class);

        $modules = ModuleModel::query()
            ->withCount(['blocks', 'fields', 'records'])
            ->orderBy('order')
            ->orderBy('name')
            ->get()
            ->map(function (ModuleModel $module) {
                return [
                    'id' => $module->id,
                    'name' => $module->name,
                    'singular_name' => $module->singular_name,
                    'api_name' => $module->api_name,
                    'icon' => $module->icon,
                    'description' => $module->description,
                    'is_active' => $module->is_active,
                    'is_system' => $module->is_system,
                    'blocks_count' => $module->blocks_count,
                    'fields_count' => $module->fields_count,
                    'records_count' => $module->records_count,
                    'created_at' => $module->created_at,
                ];
            });

        return Inertia::render('admin/modules/Index', [
            'modules' => $modules,
        ]);
    }

    /**
     * Show the form for creating a new module.
     */
    public function create(): Response
    {
        // TODO: Add authorization when policies are implemented
        // $this->authorize('create', ModuleModel::class);

        return Inertia::render('admin/modules/Create', [
            'fieldTypes' => FieldService::FIELD_TYPES,
        ]);
    }

    /**
     * Show the form for editing a module.
     */
    public function edit(int $id): Response
    {
        $module = ModuleModel::with([
            'blocks' => function ($query) {
                $query->orderBy('order');
            },
            'blocks.fields' => function ($query) {
                $query->orderBy('order');
            },
            'blocks.fields.options' => function ($query) {
                $query->orderBy('order');
            },
        ])->findOrFail($id);

        // TODO: Add authorization when policies are implemented
        // $this->authorize('update', $module);

        return Inertia::render('admin/modules/Edit', [
            'module' => [
                'id' => $module->id,
                'name' => $module->name,
                'singular_name' => $module->singular_name,
                'api_name' => $module->api_name,
                'icon' => $module->icon,
                'description' => $module->description,
                'is_active' => $module->is_active,
                'is_system' => $module->is_system,
                'settings' => $module->settings,
                'blocks' => $module->blocks->map(function ($block) {
                    return [
                        'id' => $block->id,
                        'type' => $block->type,
                        'label' => $block->label,
                        'order' => $block->order,
                        'settings' => $block->settings,
                        'fields' => $block->fields->map(function ($field) {
                            return [
                                'id' => $field->id,
                                'type' => $field->type,
                                'api_name' => $field->api_name,
                                'label' => $field->label,
                                'description' => $field->description,
                                'help_text' => $field->help_text,
                                'is_required' => $field->is_required,
                                'is_unique' => $field->is_unique,
                                'is_searchable' => $field->is_searchable,
                                'order' => $field->order,
                                'default_value' => $field->default_value,
                                'validation_rules' => $field->validation_rules,
                                'settings' => $field->settings,
                                'width' => $field->width,
                                'options' => $field->options->map(function ($option) {
                                    return [
                                        'id' => $option->id,
                                        'label' => $option->label,
                                        'value' => $option->value,
                                        'color' => $option->color,
                                        'order' => $option->order,
                                        'is_default' => $option->is_default,
                                    ];
                                }),
                            ];
                        }),
                    ];
                }),
            ],
            'fieldTypes' => FieldService::FIELD_TYPES,
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Infrastructure\Persistence\Eloquent\Models\ModuleModel;
use App\Infrastructure\Persistence\Eloquent\Models\ModuleRecordModel;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class ModuleViewController extends Controller
{
    /**
     * Display a listing of module records.
     */
    public function index(Request $request, string $moduleApiName): Response
    {
        $module = ModuleModel::with(['blocks.fields.options'])
            ->where('api_name', $moduleApiName)
            ->firstOrFail();

        $defaultViewId = $request->user()->getDefaultViewForModule($moduleApiName);

        return Inertia::render('modules/Index', [
            'module' => [
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
                    'order' => $block->order,
                    'fields' => $block->fields->map(fn ($field) => [
                        'id' => $field->id,
                        'label' => $field->label,
                        'api_name' => $field->api_name,
                        'type' => $field->type,
                        'is_required' => $field->is_required,
                        'order' => $field->order,
                        'settings' => $field->settings,
                        'options' => $field->options->map(fn ($option) => [
                            'id' => $option->id,
                            'label' => $option->label,
                            'value' => $option->value,
                            'color' => $option->color,
                        ]),
                    ]),
                ]),
            ],
            'defaultViewId' => $defaultViewId,
        ]);
    }

    /**
     * Show the form for creating a new record.
     */
    public function create(string $moduleApiName): Response
    {
        $module = ModuleModel::with(['blocks.fields.options'])
            ->where('api_name', $moduleApiName)
            ->firstOrFail();

        return Inertia::render('modules/Create', [
            'module' => $module,
        ]);
    }

    /**
     * Display the specified record.
     */
    public function show(string $moduleApiName, int $id): Response
    {
        $module = ModuleModel::with(['blocks.fields.options'])
            ->where('api_name', $moduleApiName)
            ->firstOrFail();

        $record = ModuleRecordModel::where('module_id', $module->id)
            ->where('id', $id)
            ->firstOrFail();

        return Inertia::render('modules/Show', [
            'module' => $module,
            'record' => $record,
        ]);
    }

    /**
     * Show the form for editing the specified record.
     */
    public function edit(string $moduleApiName, int $id): Response
    {
        $module = ModuleModel::with(['blocks.fields.options'])
            ->where('api_name', $moduleApiName)
            ->firstOrFail();

        $record = ModuleRecordModel::where('module_id', $module->id)
            ->where('id', $id)
            ->firstOrFail();

        return Inertia::render('modules/Edit', [
            'module' => $module,
            'record' => $record,
        ]);
    }
}

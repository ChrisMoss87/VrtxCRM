<?php

declare(strict_types=1);

namespace App\Http\Controllers\Demo;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\Models\ModuleModel;
use Inertia\Inertia;
use Inertia\Response;

final class DynamicFormController extends Controller
{
    public function index(): Response
    {
        $module = ModuleModel::with(['blocks.fields.fieldOptions'])
            ->where('name', 'Test Form')
            ->firstOrFail();

        return Inertia::render('demo/DynamicForm', [
            'module' => $module,
        ]);
    }
}

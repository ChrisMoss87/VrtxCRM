<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TableView;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class TableViewController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'module' => 'required|string',
        ]);

        $userId = Auth::id();
        $module = $request->input('module');

        $views = TableView::forModule($module)
            ->accessibleBy($userId)
            ->with('user:id,name,email')
            ->orderBy('is_default', 'desc')
            ->orderBy('name')
            ->get();

        return response()->json($views);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'module' => 'required|string|max:255',
            'description' => 'nullable|string',
            'filters' => 'nullable|array',
            'sorting' => 'nullable|array',
            'column_visibility' => 'nullable|array',
            'column_order' => 'nullable|array',
            'column_widths' => 'nullable|array',
            'page_size' => 'nullable|integer|min:10|max:500',
            'is_default' => 'boolean',
            'is_public' => 'boolean',
        ]);

        $validated['user_id'] = Auth::id();

        // If setting as default, unset other defaults for this module
        if ($validated['is_default'] ?? false) {
            TableView::forUser($validated['user_id'])
                ->forModule($validated['module'])
                ->update(['is_default' => false]);
        }

        $view = TableView::create($validated);

        return response()->json($view, 201);
    }

    public function show(TableView $tableView): JsonResponse
    {
        // Check access
        $userId = Auth::id();
        if ($tableView->user_id !== $userId && ! $tableView->is_public && ! $tableView->shares()->where('user_id', $userId)->exists()) {
            abort(403, 'Unauthorized access to this view');
        }

        return response()->json($tableView->load('user:id,name,email'));
    }

    public function update(Request $request, TableView $tableView): JsonResponse
    {
        // Check edit permission
        $userId = Auth::id();
        $canEdit = $tableView->user_id === $userId ||
            $tableView->shares()->where('user_id', $userId)->where('can_edit', true)->exists();

        if (! $canEdit) {
            abort(403, 'You do not have permission to edit this view');
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'filters' => 'nullable|array',
            'sorting' => 'nullable|array',
            'column_visibility' => 'nullable|array',
            'column_order' => 'nullable|array',
            'column_widths' => 'nullable|array',
            'page_size' => 'nullable|integer|min:10|max:500',
            'is_default' => 'boolean',
            'is_public' => 'boolean',
        ]);

        // If setting as default, unset other defaults for this module
        if (($validated['is_default'] ?? false) && $tableView->user_id === $userId) {
            TableView::forUser($userId)
                ->forModule($tableView->module)
                ->where('id', '!=', $tableView->id)
                ->update(['is_default' => false]);
        }

        $tableView->update($validated);

        return response()->json($tableView);
    }

    public function destroy(TableView $tableView): JsonResponse
    {
        // Only owner can delete
        if ($tableView->user_id !== Auth::id()) {
            abort(403, 'You do not have permission to delete this view');
        }

        $tableView->delete();

        return response()->json(['message' => 'View deleted successfully']);
    }

    public function duplicate(TableView $tableView): JsonResponse
    {
        // Check access
        $userId = Auth::id();
        if ($tableView->user_id !== $userId && ! $tableView->is_public && ! $tableView->shares()->where('user_id', $userId)->exists()) {
            abort(403, 'Unauthorized access to this view');
        }

        $newView = $tableView->replicate();
        $newView->user_id = $userId;
        $newView->name = $tableView->name.' (Copy)';
        $newView->is_default = false;
        $newView->is_public = false;
        $newView->save();

        return response()->json($newView, 201);
    }
}

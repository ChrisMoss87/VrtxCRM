<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class UserPreferenceController extends Controller
{
    /**
     * Get user preferences.
     */
    public function show(Request $request): JsonResponse
    {
        return response()->json([
            'preferences' => $request->user()->preferences ?? [],
        ]);
    }

    /**
     * Update user preferences.
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'preferences' => 'required|array',
        ]);

        $request->user()->update([
            'preferences' => $validated['preferences'],
        ]);

        return response()->json([
            'preferences' => $request->user()->preferences,
        ]);
    }

    /**
     * Set default view for a module.
     */
    public function setDefaultView(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'module' => 'required|string',
            'view_id' => 'required|integer',
        ]);

        $request->user()->setDefaultViewForModule(
            $validated['module'],
            $validated['view_id']
        );

        return response()->json([
            'success' => true,
            'preferences' => $request->user()->preferences,
        ]);
    }

    /**
     * Clear default view for a module.
     */
    public function clearDefaultView(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'module' => 'required|string',
        ]);

        $request->user()->clearDefaultViewForModule($validated['module']);

        return response()->json([
            'success' => true,
            'preferences' => $request->user()->preferences,
        ]);
    }
}

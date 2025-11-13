<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Api;

use App\Domain\Modules\Entities\Module;
use App\Domain\Modules\Repositories\ModuleRepositoryInterface;
use App\Domain\Modules\ValueObjects\ModuleSettings;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

final class ModuleManagementController extends Controller
{
    public function __construct(
        private readonly ModuleRepositoryInterface $moduleRepository
    ) {}

    /**
     * List all modules.
     */
    public function index(): JsonResponse
    {
        $modules = $this->moduleRepository->findAll();

        return response()->json([
            'modules' => array_map(fn (Module $module) => [
                'id' => $module->getId(),
                'name' => $module->getName(),
                'api_name' => $module->getApiName(),
                'singular_name' => $module->getSingularName(),
                'icon' => $module->getIcon(),
                'description' => $module->getDescription(),
                'is_active' => $module->isActive(),
                'is_system' => $module->isSystem(),
                'order' => $module->getOrder(),
                'settings' => $module->getSettings()->toArray(),
                'created_at' => $module->getCreatedAt()?->format('Y-m-d H:i:s'),
                'updated_at' => $module->getUpdatedAt()?->format('Y-m-d H:i:s'),
            ], $modules),
        ]);
    }

    /**
     * Get a single module with full structure (blocks and fields).
     */
    public function show(int $id): JsonResponse
    {
        $module = $this->moduleRepository->findById($id);

        if (! $module) {
            return response()->json(['error' => 'Module not found'], 404);
        }

        // TODO: Load blocks and fields
        return response()->json([
            'module' => [
                'id' => $module->getId(),
                'name' => $module->getName(),
                'api_name' => $module->getApiName(),
                'singular_name' => $module->getSingularName(),
                'icon' => $module->getIcon(),
                'description' => $module->getDescription(),
                'is_active' => $module->isActive(),
                'is_system' => $module->isSystem(),
                'order' => $module->getOrder(),
                'settings' => $module->getSettings()->toArray(),
                'created_at' => $module->getCreatedAt()?->format('Y-m-d H:i:s'),
                'updated_at' => $module->getUpdatedAt()?->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    /**
     * Create a new module.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'singular_name' => ['required', 'string', 'max:255'],
            'api_name' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z][a-z0-9_]*$/',
                Rule::unique('modules', 'api_name'),
            ],
            'icon' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'order' => ['integer', 'min:0'],
            'settings' => ['nullable', 'array'],
        ]);

        // Auto-generate API name if not provided
        $apiName = $validated['api_name'] ?? Str::snake(Str::plural($validated['name']));

        // Check if API name already exists
        if ($this->moduleRepository->existsByName($apiName)) {
            return response()->json([
                'error' => 'API name already exists',
                'errors' => ['api_name' => ['The API name has already been taken.']],
            ], 422);
        }

        $module = Module::create(
            name: $validated['name'],
            apiName: $apiName,
            singularName: $validated['singular_name'],
            icon: $validated['icon'] ?? null,
            description: $validated['description'] ?? null,
            isActive: $validated['is_active'] ?? true,
            isSystem: false,
            order: $validated['order'] ?? 0,
            settings: new ModuleSettings($validated['settings'] ?? [])
        );

        $savedModule = $this->moduleRepository->save($module);

        return response()->json([
            'module' => [
                'id' => $savedModule->getId(),
                'name' => $savedModule->getName(),
                'api_name' => $savedModule->getApiName(),
                'singular_name' => $savedModule->getSingularName(),
                'icon' => $savedModule->getIcon(),
                'description' => $savedModule->getDescription(),
                'is_active' => $savedModule->isActive(),
                'is_system' => $savedModule->isSystem(),
                'order' => $savedModule->getOrder(),
                'settings' => $savedModule->getSettings()->toArray(),
            ],
        ], 201);
    }

    /**
     * Update an existing module.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $module = $this->moduleRepository->findById($id);

        if (! $module) {
            return response()->json(['error' => 'Module not found'], 404);
        }

        // Prevent editing system modules
        if ($module->isSystem()) {
            return response()->json(['error' => 'Cannot edit system modules'], 403);
        }

        $validated = $request->validate([
            'name' => ['string', 'max:255'],
            'singular_name' => ['string', 'max:255'],
            'api_name' => [
                'string',
                'max:255',
                'regex:/^[a-z][a-z0-9_]*$/',
                Rule::unique('modules', 'api_name')->ignore($id),
            ],
            'icon' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'order' => ['integer', 'min:0'],
            'settings' => ['nullable', 'array'],
        ]);

        // Check if new API name conflicts
        if (isset($validated['api_name']) && $this->moduleRepository->existsByName($validated['api_name'], $id)) {
            return response()->json([
                'error' => 'API name already exists',
                'errors' => ['api_name' => ['The API name has already been taken.']],
            ], 422);
        }

        // Update module properties
        if (isset($validated['name'])) {
            $module->updateName($validated['name']);
        }

        if (isset($validated['singular_name'])) {
            $module->updateSingularName($validated['singular_name']);
        }

        if (isset($validated['api_name'])) {
            $module->updateApiName($validated['api_name']);
        }

        if (isset($validated['icon'])) {
            $module->updateIcon($validated['icon']);
        }

        if (isset($validated['description'])) {
            $module->updateDescription($validated['description']);
        }

        if (isset($validated['is_active'])) {
            if ($validated['is_active']) {
                $module->activate();
            } else {
                $module->deactivate();
            }
        }

        if (isset($validated['order'])) {
            $module->updateOrder($validated['order']);
        }

        if (isset($validated['settings'])) {
            $module->updateSettings(new ModuleSettings($validated['settings']));
        }

        $savedModule = $this->moduleRepository->save($module);

        return response()->json([
            'module' => [
                'id' => $savedModule->getId(),
                'name' => $savedModule->getName(),
                'api_name' => $savedModule->getApiName(),
                'singular_name' => $savedModule->getSingularName(),
                'icon' => $savedModule->getIcon(),
                'description' => $savedModule->getDescription(),
                'is_active' => $savedModule->isActive(),
                'is_system' => $savedModule->isSystem(),
                'order' => $savedModule->getOrder(),
                'settings' => $savedModule->getSettings()->toArray(),
            ],
        ]);
    }

    /**
     * Delete a module.
     */
    public function destroy(int $id): JsonResponse
    {
        $module = $this->moduleRepository->findById($id);

        if (! $module) {
            return response()->json(['error' => 'Module not found'], 404);
        }

        // Prevent deleting system modules
        if ($module->isSystem()) {
            return response()->json(['error' => 'Cannot delete system modules'], 403);
        }

        $this->moduleRepository->delete($id);

        return response()->json(['message' => 'Module deleted successfully']);
    }

    /**
     * Activate a module.
     */
    public function activate(int $id): JsonResponse
    {
        $module = $this->moduleRepository->findById($id);

        if (! $module) {
            return response()->json(['error' => 'Module not found'], 404);
        }

        $module->activate();
        $this->moduleRepository->save($module);

        return response()->json(['message' => 'Module activated successfully']);
    }

    /**
     * Deactivate a module.
     */
    public function deactivate(int $id): JsonResponse
    {
        $module = $this->moduleRepository->findById($id);

        if (! $module) {
            return response()->json(['error' => 'Module not found'], 404);
        }

        // Prevent deactivating system modules
        if ($module->isSystem()) {
            return response()->json(['error' => 'Cannot deactivate system modules'], 403);
        }

        $module->deactivate();
        $this->moduleRepository->save($module);

        return response()->json(['message' => 'Module deactivated successfully']);
    }
}

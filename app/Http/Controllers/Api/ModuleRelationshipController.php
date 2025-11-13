<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Domain\Modules\Entities\Relationship;
use App\Domain\Modules\Repositories\ModuleRelationshipRepositoryInterface;
use App\Domain\Modules\ValueObjects\RelationshipSettings;
use App\Http\Controllers\Controller;
use DateTimeImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Module Relationship API Controller
 *
 * Handles CRUD operations for relationships between modules.
 */
final class ModuleRelationshipController extends Controller
{
    public function __construct(
        private readonly ModuleRelationshipRepositoryInterface $repository,
    ) {}

    /**
     * Get all relationships for a module.
     */
    public function index(Request $request): JsonResponse
    {
        $moduleId = $request->query('module_id');

        if ($moduleId) {
            $relationships = $this->repository->findAllForModule((int) $moduleId);
        } else {
            // If no module_id provided, this would need pagination
            // For now, return empty array as this is module-specific
            $relationships = [];
        }

        return response()->json([
            'data' => array_map(
                fn (Relationship $rel) => $this->transformToArray($rel),
                $relationships
            ),
        ]);
    }

    /**
     * Get a single relationship by ID.
     */
    public function show(int $id): JsonResponse
    {
        $relationship = $this->repository->findById($id);

        if (! $relationship) {
            return response()->json([
                'message' => 'Relationship not found',
            ], 404);
        }

        return response()->json([
            'data' => $this->transformToArray($relationship),
        ]);
    }

    /**
     * Create a new relationship.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from_module_id' => ['required', 'integer', 'exists:modules,id'],
            'to_module_id' => ['required', 'integer', 'exists:modules,id', 'different:from_module_id'],
            'name' => ['required', 'string', 'max:255'],
            'api_name' => ['required', 'string', 'max:255', 'regex:/^[a-z_][a-z0-9_]*$/'],
            'type' => ['required', 'string', Rule::in(['one_to_many', 'many_to_many'])],
            'settings' => ['array'],
            'settings.cascade_delete' => ['boolean'],
            'settings.required' => ['boolean'],
            'settings.allow_create_related' => ['boolean'],
            'settings.display_field' => ['string'],
            'settings.sort_field' => ['string'],
            'settings.sort_direction' => ['string', Rule::in(['asc', 'desc'])],
            'settings.filters' => ['array', 'nullable'],
        ]);

        // Check if relationship already exists
        if ($this->repository->existsBetweenModules(
            $validated['from_module_id'],
            $validated['to_module_id'],
            $validated['api_name']
        )) {
            return response()->json([
                'message' => 'A relationship with this API name already exists between these modules',
                'errors' => [
                    'api_name' => ['This relationship already exists'],
                ],
            ], 422);
        }

        // Create relationship entity
        $settings = RelationshipSettings::fromArray($validated['settings'] ?? []);

        if ($validated['type'] === 'one_to_many') {
            $relationship = Relationship::createOneToMany(
                id: 0, // Will be assigned by database
                fromModuleId: $validated['from_module_id'],
                toModuleId: $validated['to_module_id'],
                name: $validated['name'],
                apiName: $validated['api_name'],
                settings: $settings,
            );
        } else {
            $relationship = Relationship::createManyToMany(
                id: 0,
                fromModuleId: $validated['from_module_id'],
                toModuleId: $validated['to_module_id'],
                name: $validated['name'],
                apiName: $validated['api_name'],
                settings: $settings,
            );
        }

        $saved = $this->repository->save($relationship);

        return response()->json([
            'data' => $this->transformToArray($saved),
            'message' => 'Relationship created successfully',
        ], 201);
    }

    /**
     * Update an existing relationship.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $existing = $this->repository->findById($id);

        if (! $existing) {
            return response()->json([
                'message' => 'Relationship not found',
            ], 404);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'settings' => ['sometimes', 'array'],
            'settings.cascade_delete' => ['boolean'],
            'settings.required' => ['boolean'],
            'settings.allow_create_related' => ['boolean'],
            'settings.display_field' => ['string'],
            'settings.sort_field' => ['string'],
            'settings.sort_direction' => ['string', Rule::in(['asc', 'desc'])],
            'settings.filters' => ['array', 'nullable'],
        ]);

        // Merge settings
        $currentSettings = $existing->settings()->toArray();
        $newSettings = array_merge($currentSettings, $validated['settings'] ?? []);

        // Create updated relationship (immutable, so we create new instance)
        $updated = new Relationship(
            id: $existing->id(),
            fromModuleId: $existing->fromModuleId(),
            toModuleId: $existing->toModuleId(),
            name: $validated['name'] ?? $existing->name(),
            apiName: $existing->apiName(),
            type: $existing->type(),
            settings: RelationshipSettings::fromArray($newSettings),
            createdAt: $existing->createdAt(),
            updatedAt: new DateTimeImmutable(),
        );

        $saved = $this->repository->save($updated);

        return response()->json([
            'data' => $this->transformToArray($saved),
            'message' => 'Relationship updated successfully',
        ]);
    }

    /**
     * Delete a relationship.
     */
    public function destroy(int $id): JsonResponse
    {
        $relationship = $this->repository->findById($id);

        if (! $relationship) {
            return response()->json([
                'message' => 'Relationship not found',
            ], 404);
        }

        $this->repository->delete($id);

        return response()->json([
            'message' => 'Relationship deleted successfully',
        ]);
    }

    /**
     * Transform relationship entity to array for API response.
     */
    private function transformToArray(Relationship $relationship): array
    {
        return [
            'id' => $relationship->id(),
            'from_module_id' => $relationship->fromModuleId(),
            'to_module_id' => $relationship->toModuleId(),
            'name' => $relationship->name(),
            'api_name' => $relationship->apiName(),
            'type' => $relationship->type()->toString(),
            'settings' => $relationship->settings()->toArray(),
            'created_at' => $relationship->createdAt()->format('c'),
            'updated_at' => $relationship->updatedAt()?->format('c'),
        ];
    }
}

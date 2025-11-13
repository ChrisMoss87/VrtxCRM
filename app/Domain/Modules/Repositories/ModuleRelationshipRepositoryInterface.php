<?php

declare(strict_types=1);

namespace App\Domain\Modules\Repositories;

use App\Domain\Modules\Entities\Relationship;

/**
 * Module Relationship Repository Interface
 *
 * Defines the contract for persisting and retrieving relationships.
 * This is a port in hexagonal architecture.
 */
interface ModuleRelationshipRepositoryInterface
{
    /**
     * Find a relationship by ID.
     */
    public function findById(int $id): ?Relationship;

    /**
     * Find a relationship by API name.
     */
    public function findByApiName(string $apiName): ?Relationship;

    /**
     * Get all relationships for a specific module.
     *
     * @return array<Relationship>
     */
    public function findByFromModule(int $moduleId): array;

    /**
     * Get all relationships pointing to a specific module.
     *
     * @return array<Relationship>
     */
    public function findByToModule(int $moduleId): array;

    /**
     * Get all relationships (both from and to) for a module.
     *
     * @return array<Relationship>
     */
    public function findAllForModule(int $moduleId): array;

    /**
     * Save a relationship (create or update).
     */
    public function save(Relationship $relationship): Relationship;

    /**
     * Delete a relationship by ID.
     */
    public function delete(int $id): bool;

    /**
     * Check if a relationship exists between two modules.
     */
    public function existsBetweenModules(int $fromModuleId, int $toModuleId, string $apiName): bool;
}

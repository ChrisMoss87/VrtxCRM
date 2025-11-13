<?php

declare(strict_types=1);

namespace App\Policies;

use App\Infrastructure\Persistence\Eloquent\Models\ModuleRecordModel;
use App\Models\User;

final class ModuleRecordPolicy
{
    /**
     * Determine whether the user can view any records.
     */
    public function viewAny(User $user, string $moduleApiName): bool
    {
        // Check global permission first
        if ($user->can('view_any_record')) {
            return true;
        }

        // Check module-specific permission
        return $user->can("modules.{$moduleApiName}.view");
    }

    /**
     * Determine whether the user can view the record.
     */
    public function view(User $user, ModuleRecordModel $record): bool
    {
        // Check global permission first
        if ($user->can('view_any_record')) {
            return true;
        }

        $moduleApiName = $record->module->api_name;

        // Check module-specific permission
        if ($user->can("modules.{$moduleApiName}.view")) {
            return true;
        }

        // Check if user owns the record (created_by field)
        if (isset($record->data['created_by']) && $record->data['created_by'] === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create records.
     */
    public function create(User $user, string $moduleApiName): bool
    {
        return $user->can("modules.{$moduleApiName}.create");
    }

    /**
     * Determine whether the user can update the record.
     */
    public function update(User $user, ModuleRecordModel $record): bool
    {
        // Check global permission first
        if ($user->can('edit_any_record')) {
            return true;
        }

        $moduleApiName = $record->module->api_name;

        // Check module-specific permission
        if ($user->can("modules.{$moduleApiName}.edit")) {
            return true;
        }

        // Check if user owns the record
        if (isset($record->data['created_by']) && $record->data['created_by'] === $user->id) {
            return $user->can("modules.{$moduleApiName}.create"); // Can edit own if can create
        }

        return false;
    }

    /**
     * Determine whether the user can delete the record.
     */
    public function delete(User $user, ModuleRecordModel $record): bool
    {
        // Check global permission first
        if ($user->can('delete_any_record')) {
            return true;
        }

        $moduleApiName = $record->module->api_name;

        // Check module-specific permission
        return $user->can("modules.{$moduleApiName}.delete");
    }
}

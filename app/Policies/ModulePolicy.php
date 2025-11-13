<?php

declare(strict_types=1);

namespace App\Policies;

use App\Infrastructure\Persistence\Eloquent\Models\ModuleModel;
use App\Models\User;

final class ModulePolicy
{
    /**
     * Determine whether the user can view any modules.
     */
    public function viewAny(User $user): bool
    {
        return true; // All authenticated users can view module list
    }

    /**
     * Determine whether the user can view the module.
     */
    public function view(User $user, ModuleModel $module): bool
    {
        return true; // All authenticated users can view module details
    }

    /**
     * Determine whether the user can create modules.
     */
    public function create(User $user): bool
    {
        return $user->can('admin.modules.manage');
    }

    /**
     * Determine whether the user can update the module.
     */
    public function update(User $user, ModuleModel $module): bool
    {
        // Cannot edit system modules
        if ($module->is_system) {
            return false;
        }

        return $user->can('admin.modules.manage');
    }

    /**
     * Determine whether the user can delete the module.
     */
    public function delete(User $user, ModuleModel $module): bool
    {
        // Cannot delete system modules
        if ($module->is_system) {
            return false;
        }

        return $user->can('admin.modules.manage');
    }

    /**
     * Determine whether the user can manage module fields.
     */
    public function manageFields(User $user, ModuleModel $module): bool
    {
        // Cannot manage fields in system modules
        if ($module->is_system) {
            return false;
        }

        return $user->can('admin.modules.manage');
    }
}

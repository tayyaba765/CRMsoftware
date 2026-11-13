<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

class RolePolicy
{
    // Can open the roles page
    public function viewAny(User $user): bool
    {
        return true;
    }

    // Can view a role
    public function view(User $user, Role $target): bool
    {
        // Super Admin and CRM Admin can view all roles
        if ($user->hasProtectedRole()) {
            return true;
        }

        // All target roles must be descendants of at least one
        // role assigned to the current user
        return $this->isChildRole($user, $target);
    }

    // Can create roles
    public function create(User $user): bool
    {
        return $user->hasProtectedRole()
            || $user->can('manage roles');
    }

    // Can update a role
    public function update(User $user, Role $target): bool
    {
        // Protected roles cannot be edited
        if ($target->isProtected()) {
            return false;
        }

        // Super Admin and CRM Admin can edit non-protected roles
        if ($user->hasProtectedRole()) {
            return true;
        }

        return $this->isChildRole($user, $target);
    }

    // Can delete a role
    public function delete(User $user, Role $target): bool
    {
        // Protected roles cannot be deleted
        if ($target->isProtected()) {
            return false;
        }

        // Super Admin and CRM Admin can delete non-protected roles
        if ($user->hasProtectedRole()) {
            return true;
        }

        return $this->isChildRole($user, $target);
    }

    /**
     * Check whether the target role is below
     * at least one role assigned to the current user.
     */
    private function isChildRole(User $user, Role $target): bool
    {
        $myRoles = $user->roles;

        // A user without roles cannot manage child roles
        if ($myRoles->isEmpty()) {
            return false;
        }

        // Start from the target role's parent
        $parentId = $target->parent_role_id;

        // Walk up the complete hierarchy
        while ($parentId !== null) {

            // Check against every role assigned to the current user
            foreach ($myRoles as $myRole) {
                if ((int) $parentId === (int) $myRole->id) {
                    return true;
                }
            }

            // Find the next parent role
            $parent = Role::find($parentId);

            if (!$parent) {
                break;
            }

            $parentId = $parent->parent_role_id;
        }

        return false;
    }
}
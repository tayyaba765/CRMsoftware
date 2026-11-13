<?php

namespace App\Policies;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OrganizationPolicy
{
    /**
     * Can the user open the organizations page?
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Can the user view this organization?
     */
    public function view(User $user, Organization $organization): bool
    {
        // CRM Admin and Super Admin can view everything
        if (
            $user->hasRole('crmadmin') ||
            $user->hasRole('superadmin') ||
            $user->hasProtectedRole()
        ) {
            return true;
        }

        // Other users cannot view closed organizations
        if (strtolower((string) $organization->status) === 'closed') {
            return false;
        }

        // Other users can view other organizations
        return true;
    }

    /**
     * Can the user create an organization?
     */
    public function create(User $user): bool
    {
        return $user->hasRole('crmadmin') ||
               $user->hasRole('superadmin') ||
               $user->hasProtectedRole();
    }

    /**
     * Can the user update this organization?
     */
    public function update(User $user, Organization $organization): bool
    {
        // CRM Admin and Super Admin can edit everything
        if (
            $user->hasRole('crmadmin') ||
            $user->hasRole('superadmin') ||
            $user->hasProtectedRole()
        ) {
            return true;
        }

        // Other users cannot edit closed organizations
        if (strtolower((string) $organization->status) === 'closed') {
            return false;
        }

        // Other users can edit non-closed organizations
        return true;
    }

    /**
     * Can the user delete this organization?
     */
    public function delete(User $user, Organization $organization): bool
    {
        // Only CRM Admin and Super Admin can delete
        return $user->hasRole('crmadmin') ||
               $user->hasRole('superadmin') ||
               $user->hasProtectedRole();
    }

    /**
     * Can the user restore this organization?
     */
    public function restore(User $user, Organization $organization): bool
    {
        return $user->hasRole('crmadmin') ||
               $user->hasRole('superadmin') ||
               $user->hasProtectedRole();
    }

    /**
     * Can the user permanently delete this organization?
     */
    public function forceDelete(User $user, Organization $organization): bool
    {
        return $user->hasRole('superadmin') ||
               ($user->roles && $user->roles->contains(function ($role) {
                   return str_replace([' ', '-', '_'], '', strtolower($role->name)) === 'superadmin';
               }));
    }
}

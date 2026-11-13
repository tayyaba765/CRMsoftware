<?php

namespace App\Policies;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserProfilePolicy
{
    /**
     * Can the user open the profiles page?
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Can the user view this profile?
     */
    public function view(User $user, Profile $profile): bool
    {
        // CRM Admin and Super Admin can view everything
        if (
            $user->hasRole('crmadmin') ||
            $user->hasRole('superadmin')
        ) {
            return true;
        }

        // Other users cannot view closed profiles
        if ($profile->status === 'closed') {
            return false;
        }

        // Other users can view other profiles
        return true;
    }

    /**
     * Can the user create a profile?
     */
    public function create(User $user): bool
    {
        return $user->hasRole('crmadmin') ||
               $user->hasRole('superadmin');
    }

    /**
     * Can the user update this profile?
     */
    public function update(User $user, Profile $profile): bool
    {
        // CRM Admin and Super Admin can edit everything
        if (
            $user->hasRole('crmadmin') ||
            $user->hasRole('superadmin')
        ) {
            return true;
        }

        // Other users cannot edit closed profiles
        if ($profile->status === 'closed') {
            return false;
        }

        // Other users can edit non-closed profiles
        return true;
    }

    /**
     * Can the user delete this profile?
     */
    public function delete(User $user, Profile $profile): bool
    {
        // Only CRM Admin and Super Admin can delete
        return $user->hasRole('crmadmin') ||
               $user->hasRole('superadmin');
    }

    /**
     * Can the user restore this profile?
     */
    public function restore(User $user, Profile $profile): bool
    {
        return $user->hasRole('crmadmin') ||
               $user->hasRole('superadmin');
    }

    /**
     * Can the user permanently delete this profile?
     */
    public function forceDelete(User $user, Profile $profile): bool
    {
        return $user->hasRole('superadmin');
    }
}

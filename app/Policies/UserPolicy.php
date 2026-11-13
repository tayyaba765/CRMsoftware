<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

class UserPolicy
{
    // Can open the users page
    public function viewAny(User $user): bool
    {
           // Super Admin and CRM Admin can access the users page.
 return true;
    }

    // Can view a user
// Can view a user.
public function view(User $user, User $target): bool
{
    // Super Admin and CRM Admin can view all users.
  if ($user->hasProtectedRole()) {
        return true;
    }

    // Users can view their own profile.
   if ($user->id === $target->id) {
        return true;
  }

    // Users with the manage users permission can view users.
    if ($user->can('manage users')) {
        return true;
    }

    // Allow users who have a parent-child role relationship.
    if ($this->isParentOfUser($user, $target)) {
       return true;
    }

    // Deny everyone else.
   return false;
  // return $this->isParentOfUser($user, $target);
    // Everyone else can view only users with child roles.
      // Super Admin and CRM Admin can view all users.

}
    // Can create users
    public function create(User $user): bool
    {
        return $user->hasProtectedRole()
            || $user->can('manage users');
    }

    // Can edit a user
    public function update(User $user, User $target): bool
    {
        if ($user->id == $target->id) {
            return false;
        }

        if ($target->hasProtectedRole()) {
            return false;
        }

        if ($user->hasProtectedRole()) {
            return true;
        }

        return $this->isParentOfUser($user, $target);
    }

    // Can delete a user
    public function delete(User $user, User $target): bool
    {
        if ($user->id == $target->id) {
            return false;
        }

        if ($target->hasProtectedRole()) {
            return false;
        }

        if ($user->hasProtectedRole()) {
            return true;
        }

        return $this->isParentOfUser($user, $target);
    }

    // Check whether the current user is above the target user's roles




private function isParentOfUser(User $user, User $target): bool
{
    // Get all roles of the logged-in user
    $myRoles = $user->roles;

    // Get all roles of the target user
    $targetRoles = $target->roles;

    // A target with no roles cannot pass this check
    if ($targetRoles->isEmpty()) {
        return false;
    }

    // Check EVERY role assigned to the target user
    foreach ($targetRoles as $targetRole) {

        $isChildRole = false;

        // Check every role assigned to the current user
        foreach ($myRoles as $myRole) {

            $parentId = $targetRole->parent_role_id;

            // Walk up the target role's hierarchy
            while ($parentId !== null) {

                if ((int) $parentId === (int) $myRole->id) {
                    $isChildRole = true;
                    break;
                }

                $parent = Role::find($parentId);

                if (!$parent) {
                    break;
                }

                $parentId = $parent->parent_role_id;
            }

            if ($isChildRole) {
                break;
            }
        }

        // If even ONE target role is unrelated, deny access
        if (!$isChildRole) {
            return false;
        }
    }

    // Every target role is under at least one of the current user's roles
    return true;
}


    //code for if 1 of my target users roles is child or descendant of one of my roles
/*
    private function isParentOfUser(User $user, User $target): bool
    {
        $myRoleIds = $user->roles->pluck('id')->toArray();

        foreach ($target->roles as $role) {
            $parentId = $role->parent_role_id;

            while ($parentId != null) {
                if (in_array($parentId, $myRoleIds)) {
                    return true;
                }

                $parent = Role::find($parentId);

                if (!$parent) {
                    break;
                }

                $parentId = $parent->parent_role_id;
            }
        }

        return false;
    }

    */



}

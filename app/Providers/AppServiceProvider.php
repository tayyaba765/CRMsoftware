<?php

namespace App\Providers;
use App\Models\Organization;
use App\Models\Profile;
use App\Policies\OrganizationPolicy;
use App\Policies\UserProfilePolicy;
use App\Models\Role;
use App\Policies\RolePolicy;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{

    public function register(): void
    {
    }

    public function boot(): void
    {
        Gate::policy(Profile::class, UserProfilePolicy::class);
        Gate::policy(Organization::class, OrganizationPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(User::class, UserPolicy::class);
    }
}

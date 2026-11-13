<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class ModuleRoleSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // -------------------------------------------------
        // 1. Fetch Modules
        // -------------------------------------------------
        $adminModule      = Module::where('name', 'Administration')->firstOrFail();
        $salesModule      = Module::where('name', 'Sales')->firstOrFail();
        $marketingModule  = Module::where('name', 'Marketing')->firstOrFail();
        $financeModule    = Module::where('name', 'Finance')->firstOrFail();
        $operationsModule = Module::where('name', 'Operations')->firstOrFail();
        $analyticsModule  = Module::where('name', 'Analytics')->firstOrFail();

        // -------------------------------------------------
        // 2. Roles (Hierarchy via parent_role_id)
        // -------------------------------------------------

        // Root Role
        $superAdmin = Role::firstOrCreate(
            ['name' => 'superadmin', 'guard_name' => 'web'],
            ['module_id' => $adminModule->id, 'parent_role_id' => null]
        );

        // Core CRM Admin (Child of Super Admin)
        $crmAdmin = Role::firstOrCreate(
            ['name' => 'crmadmin', 'guard_name' => 'web'],
            ['module_id' => $adminModule->id, 'parent_role_id' => $superAdmin->id]
        );

        // Department Managers (Children of CRM Admin)
        $salesManager = Role::firstOrCreate(
            ['name' => 'salesmanager', 'guard_name' => 'web'],
            ['module_id' => $salesModule->id, 'parent_role_id' => $crmAdmin->id]
        );

        $marketingManager = Role::firstOrCreate(
            ['name' => 'marketingmanager', 'guard_name' => 'web'],
            ['module_id' => $marketingModule->id, 'parent_role_id' => $crmAdmin->id]
        );

        $financeManager = Role::firstOrCreate(
            ['name' => 'financemanager', 'guard_name' => 'web'],
            ['module_id' => $financeModule->id, 'parent_role_id' => $crmAdmin->id]
        );

        $operationManager = Role::firstOrCreate(
            ['name' => 'operationmanager', 'guard_name' => 'web'],
            ['module_id' => $operationsModule->id, 'parent_role_id' => $crmAdmin->id]
        );

        $analyst = Role::firstOrCreate(
            ['name' => 'analyst', 'guard_name' => 'web'],
            ['module_id' => $analyticsModule->id, 'parent_role_id' => $crmAdmin->id]
        );

        // Sales Hierarchy (Team Leader under Sales Manager, Sales Agent under Team Leader)
        $teamLeader = Role::firstOrCreate(
            ['name' => 'teamleader', 'guard_name' => 'web'],
            ['module_id' => $salesModule->id, 'parent_role_id' => $salesManager->id]
        );

        $salesAgent = Role::firstOrCreate(
            ['name' => 'salesagent', 'guard_name' => 'web'],
            ['module_id' => $salesModule->id, 'parent_role_id' => $teamLeader->id]
        );

        // Department Staff/Agents
        $marketingAgent = Role::firstOrCreate(
            ['name' => 'marketingagent', 'guard_name' => 'web'],
            ['module_id' => $marketingModule->id, 'parent_role_id' => $marketingManager->id]
        );

        $financeAgent = Role::firstOrCreate(
            ['name' => 'financeagent', 'guard_name' => 'web'],
            ['module_id' => $financeModule->id, 'parent_role_id' => $financeManager->id]
        );

        $operationStaff = Role::firstOrCreate(
            ['name' => 'operationstaff', 'guard_name' => 'web'],
            ['module_id' => $operationsModule->id, 'parent_role_id' => $operationManager->id]
        );
  $new = Role::firstOrCreate(
            ['name' => 'user', 'guard_name' => 'web'],
            ['module_id' => $operationsModule->id, 'parent_role_id' => $crmAdmin->id]
        );

        // -------------------------------------------------
        // 3. Assign Permissions to Roles
        // -------------------------------------------------

        // Super Admin gets all permissions
        $superAdmin->syncPermissions(Permission::all());

        // CRM Admin
        $crmAdmin->syncPermissions([
            'manage users',
            'manage audit logs',
            'manage integrations',
            'view all leads',
            'assign leads',
            'manage deals and pipeline',
            'view sales reports',
            'manage campaigns',
            'view marketing analytics',
            'view invoices',
            'approve refunds and credits',
            'view financial reports',
            'manage customer onboarding',
            'manage service contracts',
            'view operations reports',
            'view all analytics',
            'export reporting data',
        ]);

        // Sales Manager
        $salesManager->syncPermissions([
            'view all leads',
            'create leads',
            'edit leads',
            'delete leads',
            'assign leads',
            'manage deals and pipeline',
            'approve discounts',
            'view sales reports',
            'export reporting data',
        ]);

        // Team Leader
        $teamLeader->syncPermissions([
            'view team leads',
            'create leads',
            'edit leads',
            'assign leads',
            'manage deals and pipeline',
            'view sales reports',
        ]);

        // Sales Agent
        $salesAgent->syncPermissions([
            'view own leads',
            'create leads',
            'edit leads',
            'manage deals and pipeline',
        ]);

        // Marketing Manager
        $marketingManager->syncPermissions([
            'manage campaigns',
            'view campaigns',
            'manage customer segments',
            'manage marketing templates',
            'view marketing analytics',
            'create leads',
            'export reporting data',
        ]);

        // Marketing Agent
        $marketingAgent->syncPermissions([
            'view campaigns',
            'manage marketing templates',
            'create leads',
            'view marketing analytics',
        ]);

        // Finance Manager
        $financeManager->syncPermissions([
            'view invoices',
            'create invoices',
            'manage payments',
            'approve refunds and credits',
            'view financial reports',
            'export reporting data',
        ]);

        // Finance Agent
        $financeAgent->syncPermissions([
            'view invoices',
            'create invoices',
            'manage payments',
        ]);

        // Operation Manager
        $operationManager->syncPermissions([
            'manage customer onboarding',
            'manage service contracts',
            'manage support tickets',
            'assign operations tasks',
            'view operations reports',
            'export reporting data',
        ]);

        // Operation Staff
        $operationStaff->syncPermissions([
            'manage customer onboarding',
            'manage support tickets',
        ]);

        // Analyst
        $analyst->syncPermissions([
            'view sales reports',
            'view marketing analytics',
            'view financial reports',
            'view operations reports',
            'view all analytics',
            'create custom reports',
            'export reporting data',
        ]);

$new->syncPermissions();
        $this->command->info('✅ CRM Modules, roles, and permissions seeded successfully.');
    }
}
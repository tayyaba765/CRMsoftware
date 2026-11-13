<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\Permission;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Administration
            ['name' => 'manage system settings',       'module' => 'Administration'],
            ['name' => 'manage users',                 'module' => 'Administration'],
            ['name' => 'manage roles and permissions', 'module' => 'Administration'],
            ['name' => 'manage audit logs',            'module' => 'Administration'],
            ['name' => 'manage integrations',          'module' => 'Administration'],

            // Sales
            ['name' => 'view all leads',               'module' => 'Sales'],
            ['name' => 'view team leads',              'module' => 'Sales'],
            ['name' => 'view own leads',               'module' => 'Sales'],
            ['name' => 'create leads',                 'module' => 'Sales'],
            ['name' => 'edit leads',                   'module' => 'Sales'],
            ['name' => 'delete leads',                 'module' => 'Sales'],
            ['name' => 'assign leads',                 'module' => 'Sales'],
            ['name' => 'manage deals and pipeline',    'module' => 'Sales'],
            ['name' => 'approve discounts',            'module' => 'Sales'],
            ['name' => 'view sales reports',           'module' => 'Sales'],

            // Marketing
            ['name' => 'manage campaigns',             'module' => 'Marketing'],
            ['name' => 'view campaigns',               'module' => 'Marketing'],
            ['name' => 'manage customer segments',     'module' => 'Marketing'],
            ['name' => 'manage marketing templates',   'module' => 'Marketing'],
            ['name' => 'view marketing analytics',     'module' => 'Marketing'],

            // Finance
            ['name' => 'view invoices',                'module' => 'Finance'],
            ['name' => 'create invoices',              'module' => 'Finance'],
            ['name' => 'manage payments',              'module' => 'Finance'],
            ['name' => 'approve refunds and credits',  'module' => 'Finance'],
            ['name' => 'view financial reports',       'module' => 'Finance'],

            // Operations
            ['name' => 'manage customer onboarding',   'module' => 'Operations'],
            ['name' => 'manage service contracts',     'module' => 'Operations'],
            ['name' => 'manage support tickets',       'module' => 'Operations'],
            ['name' => 'assign operations tasks',      'module' => 'Operations'],
            ['name' => 'view operations reports',      'module' => 'Operations'],

            // Analytics
            ['name' => 'view all analytics',           'module' => 'Analytics'],
            ['name' => 'create custom reports',        'module' => 'Analytics'],
            ['name' => 'export reporting data',        'module' => 'Analytics'],
        ];

        $modules = Module::whereIn('name', [
            'Administration',
            'Sales',
            'Marketing',
            'Finance',
            'Operations',
            'Analytics',
        ])->pluck('id', 'name');

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                [
                    'name' => $permission['name'],
                    'guard_name' => 'web',
                ],
                [
                    'module_id' => $modules[$permission['module']] ?? null,
                ]
            );
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
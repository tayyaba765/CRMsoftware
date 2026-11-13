<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name'  => 'Super Admin',
                'email' => 'superadmin@example.com',
                'role'  => 'superadmin',
            ],
            [
                'name'  => 'CRM Admin',
                'email' => 'crmadmin@example.com',
                'role'  => 'crmadmin',
            ],
            [
                'name'  => 'Sales Manager',
                'email' => 'salesmanager@example.com',
                'role'  => 'salesmanager',
            ],
            [
                'name'  => 'Sales Team Leader',
                'email' => 'teamleader@example.com',
                'role'  => 'teamleader',
            ],
            [
                'name'  => 'Sales Agent',
                'email' => 'salesagent@example.com',
                'role'  => 'salesagent',
            ],
            [
                'name'  => 'Marketing Manager',
                'email' => 'marketingmanager@example.com',
                'role'  => 'marketingmanager',
            ],
            [
                'name'  => 'Marketing Agent',
                'email' => 'marketingagent@example.com',
                'role'  => 'marketingagent',
            ],
            [
                'name'  => 'Finance Manager',
                'email' => 'financemanager@example.com',
                'role'  => 'financemanager',
            ],
            [
                'name'  => 'Finance Agent',
                'email' => 'financeagent@example.com',
                'role'  => 'financeagent',
            ],
            [
                'name'  => 'Operations Manager',
                'email' => 'operationmanager@example.com',
                'role'  => 'operationmanager',
            ],
            [
                'name'  => 'Operations Staff',
                'email' => 'operationstaff@example.com',
                'role'  => 'operationstaff',
            ],
            [
                'name'  => 'Business Analyst',
                'email' => 'analyst@example.com',
                'role'  => 'analyst',
            ],
        ];

        $defaultPassword = Hash::make('password');

        foreach ($users as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name'     => $data['name'],
                    'password' => $defaultPassword,
                ]
            );

            $user->syncRoles([$data['role']]);
        }

        $this->command->info('✅ CRM users seeded and roles assigned successfully.');
    }
}
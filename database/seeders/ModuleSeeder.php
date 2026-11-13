<?php

namespace Database\Seeders;

use App\Models\Module;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            'Administration',
            'Sales',
            'Marketing',
            'Finance',
            'Operations',
            'Analytics',
        ];

        foreach ($modules as $module) {
            Module::firstOrCreate([
                'name' => $module,
            ]);
        }
    }
}
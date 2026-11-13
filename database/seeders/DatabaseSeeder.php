<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
    $this->call([
           ModuleSeeder::class,
          PermissionSeeder::class, 
           ModuleRoleSeeder::class,
        
          
                  UserSeeder::class,
        ]);
     User::firstOrCreate(
    // 1. Search by these unique attributes
    ['email' => 'test@example.com'], 
    
    // 2. If not found, create with these attributes
    [
        'name' => 'Test User',
        'password' => 'password', 
    ]
);
    }
}

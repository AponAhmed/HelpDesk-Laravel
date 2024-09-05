<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        // \App\Models\User::factory(10)->create();
        \App\Models\UserRole::create(['name' => "Super Admin", 'guard_name' => 'web']);
        \App\Models\UserRole::create(['name' => "Admin", 'guard_name' => 'web']);
        \App\Models\UserRole::create(['name' => "Merchandiser", 'guard_name' => 'web']);
        \App\Models\UserHasRole::create(['user_id' => 1, 'role_id' => 1]);
    }
}

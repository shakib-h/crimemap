<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'Admin', 'slug' => 'admin'],
            ['name' => 'Moderator', 'slug' => 'moderator'],
            ['name' => 'User', 'slug' => 'user'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(
                ['slug' => $role['slug']],  // search by
                ['name' => $role['name']]   // create with
            );
        }
    }
}
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['rolename' => 'Admin', 'ispredefined' => true, 'created_at' => now()],
            ['rolename' => 'Initiator', 'ispredefined' => true, 'created_at' => now()],
            ['rolename' => 'School', 'ispredefined' => true, 'created_at' => now()],
            ['rolename' => 'Accountant', 'ispredefined' => true, 'created_at' => now()],
        ];

        DB::table('role')->insert($roles);
    }
}

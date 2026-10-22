<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        // Get role IDs
        $adminRoleId = DB::table('role')->where('rolename', 'Admin')->first()->role_id;
        $initiatorRoleId = DB::table('role')->where('rolename', 'Initiator')->first()->role_id;
        $schoolRoleId = DB::table('role')->where('rolename', 'School')->first()->role_id;
        $accountantRoleId = DB::table('role')->where('rolename', 'Accountant')->first()->role_id;

        // Create test users
        DB::table('sysuser')->insert([
            [
                'role_id' => $adminRoleId,
                'fname' => 'Admin',
                'lname' => 'User',
                'username' => 'admin',
                'pswd' => Hash::make('password'),
                'pin' => Hash::make('1234'),
                'email' => 'admin@example.com',
                'inactive' => false,
                'created_at' => now(),
            ],
            [
                'role_id' => $initiatorRoleId,
                'fname' => 'Initiator',
                'lname' => 'User',
                'username' => 'initiator',
                'pswd' => Hash::make('password'),
                'pin' => Hash::make('1234'),
                'email' => 'initiator@example.com',
                'inactive' => false,
                'created_at' => now(),
            ],
            [
                'role_id' => $accountantRoleId,
                'fname' => 'Accountant',
                'lname' => 'User',
                'username' => 'accountant',
                'pswd' => Hash::make('password'),
                'pin' => Hash::make('1234'),
                'email' => 'accountant@example.com',
                'inactive' => false,
                'created_at' => now(),
            ],
        ]);

        // Get necessary IDs for school creation
        $schoolTypeId = DB::table('schooltype')->first()->schooltype_id;
        $eduboardId = DB::table('ms_eductionalboard')->first()->eduboard_id;
        $stateId = DB::table('ms_state')->first()->state_id;
        
        // Create a district
        $distId = DB::table('ms_dist')->insertGetId([
            'state_id' => $stateId,
            'dname' => 'Ahmedabad',
            'created_at' => now(),
        ]);
        
        // Create a taluka
        $talukaId = DB::table('ms_taluko')->insertGetId([
            'dist_id' => $distId,
            'tname' => 'Ahmedabad City',
            'created_at' => now(),
        ]);

        // Create test school
        $schoolId = DB::table('school')->insertGetId([
            'schooltype_id' => $schoolTypeId,
            'eduboard_id' => $eduboardId,
            'schoolname' => 'Test School',
            'takula_id' => $talukaId,
            'adminname' => 'School Admin',
            'admincontact' => '9876543210',
            'inactive' => false,
            'status' => 1,
            'created_at' => now(),
        ]);

        // Create semester batch
        DB::table('sembatches')->insert([
            'start_date' => now(),
            'end_date' => now()->addMonths(6),
            'capacity' => 100,
            'fees' => 5000.00,
            'code_prefix' => 'SEM2025',
            'status' => 0,
            'created_at' => now(),
        ]);
    }
}

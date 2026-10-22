<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Get role IDs
        $adminRole = DB::table('role')->where('rolename', 'Admin')->first();
        $initiatorRole = DB::table('role')->where('rolename', 'Initiator')->first();
        $schoolRole = DB::table('role')->where('rolename', 'School')->first();
        $accountantRole = DB::table('role')->where('rolename', 'Accountant')->first();

        if (!$adminRole || !$initiatorRole || !$schoolRole || !$accountantRole) {
            return;
        }

        $adminRoleId = $adminRole->role_id;
        $initiatorRoleId = $initiatorRole->role_id;
        $schoolRoleId = $schoolRole->role_id;
        $accountantRoleId = $accountantRole->role_id;

        // Get all permission IDs
        $allPermissions = DB::table('permission')->get();
        
        // Admin gets all permissions
        foreach ($allPermissions as $permission) {
            DB::table('rolepermission')->updateOrInsert(
                ['role_id' => $adminRoleId, 'permission_id' => $permission->permission_id],
                ['role_id' => $adminRoleId, 'permission_id' => $permission->permission_id]
            );
        }

        // Initiator permissions
        $initiatorPermKeys = [
            10041, 10042, 10043, // Students: Read, Write, Manage
            10051, // Dashboard: Read
            10091, 10092, // Student Batches: Read, Write
            10101, 10102, // Guardians: Read, Write
            10201, // Lead Management (Initiator access)
        ];
        foreach ($initiatorPermKeys as $permKey) {
            $permission = DB::table('permission')->where('permkey', $permKey)->first();
            if ($permission) {
                DB::table('rolepermission')->updateOrInsert(
                    ['role_id' => $initiatorRoleId, 'permission_id' => $permission->permission_id],
                    ['role_id' => $initiatorRoleId, 'permission_id' => $permission->permission_id]
                );
            }
        }

        // School permissions
        $schoolPermKeys = [
            10041, 10042, // Students: Read, Write
            10051, // Dashboard: Read
            10061, 10062, // Invoices: Read, Write
            10071, 10072, // Payments: Read, Write
            10101, 10102, // Guardians: Read, Write
            10161, 10162, // Fee Plans: Read, Write (for school to set fees)
        ];
        foreach ($schoolPermKeys as $permKey) {
            $permission = DB::table('permission')->where('permkey', $permKey)->first();
            if ($permission) {
                DB::table('rolepermission')->updateOrInsert(
                    ['role_id' => $schoolRoleId, 'permission_id' => $permission->permission_id],
                    ['role_id' => $schoolRoleId, 'permission_id' => $permission->permission_id]
                );
            }
        }

        // Accountant permissions
        $accountantPermKeys = [
            10031, // Schools: Read only
            10041, // Students: Read only
            10051, // Dashboard: Read
            10061, 10062, 10063, // Invoices: Read, Write, Manage
            10071, 10072, 10073, // Payments: Read, Write, Manage
            10081, 10082, 10083, // Upload Batches: Read, Write, Manage
        ];
        foreach ($accountantPermKeys as $permKey) {
            $permission = DB::table('permission')->where('permkey', $permKey)->first();
            if ($permission) {
                DB::table('rolepermission')->updateOrInsert(
                    ['role_id' => $accountantRoleId, 'permission_id' => $permission->permission_id],
                    ['role_id' => $accountantRoleId, 'permission_id' => $permission->permission_id]
                );
            }
        }
    }
}

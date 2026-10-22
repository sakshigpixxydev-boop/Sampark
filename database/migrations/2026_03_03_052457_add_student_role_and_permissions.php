<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if Student role already exists
        $role = DB::table('role')->where('rolename', 'Student')->first();
        
        if (!$role) {
            $roleId = DB::table('role')->insertGetId([
                'rolename' => 'Student',
                'ispredefined' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $roleId = $role->role_id;
        }

        // Assign 'Read Dashboard' (permkey: 10051) to Student role
        // We first find the permission id for permkey 10051
        $permission = DB::table('permission')->where('permkey', 10051)->first();
        
        if ($permission) {
            DB::table('rolepermission')->insert([
                'role_id' => $roleId,
                'permission_id' => $permission->permission_id,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $role = DB::table('role')->where('rolename', 'Student')->first();
        if ($role) {
            DB::table('rolepermission')->where('role_id', $role->role_id)->delete();
            DB::table('role')->where('role_id', $role->role_id)->delete();
        }
    }
};

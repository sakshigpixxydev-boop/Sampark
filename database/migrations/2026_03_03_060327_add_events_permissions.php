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
        $permissions = [
            ['permkey' => 10401, 'permname' => 'Read Events'],
            ['permkey' => 10402, 'permname' => 'Write Events'],
            ['permkey' => 10403, 'permname' => 'Manage Events'],
        ];

        foreach ($permissions as $permission) {
            // Use updateOrInsert to handle both new and existing
            DB::table('permission')->updateOrInsert(
                ['permkey' => $permission['permkey']],
                [
                    'permname' => $permission['permname'],
                    'created_at' => now(),
                ]
            );

            $perm = DB::table('permission')->where('permkey', $permission['permkey'])->first();

            // Assign to Super Admin role (role_id 1)
            $role = DB::table('role')->where('role_id', 1)->first();
            if ($role && $perm) {
                DB::table('rolepermission')->updateOrInsert([
                    'role_id' => $role->role_id,
                    'permission_id' => $perm->permission_id,
                ]);
            }
        }

        // Remove the 10404 if it exists from previous run
        $oldPerm = DB::table('permission')->where('permkey', 10404)->first();
        if ($oldPerm) {
            DB::table('rolepermission')->where('permission_id', $oldPerm->permission_id)->delete();
            DB::table('permission')->where('permission_id', $oldPerm->permission_id)->delete();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $permKeys = [10401, 10402, 10403];
        
        $permissionIds = DB::table('permission')
            ->whereIn('permkey', $permKeys)
            ->pluck('permission_id');

        DB::table('rolepermission')->whereIn('permission_id', $permissionIds)->delete();
        DB::table('permission')->whereIn('permission_id', $permissionIds)->delete();
    }
};

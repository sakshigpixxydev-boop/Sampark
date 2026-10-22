<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Permkeys 11701+ — 11606 (Educator) was the previous high-water mark.
     */
    private array $permissions = [
        ['permkey' => 11701, 'permname' => 'Read Student Leads'],
        ['permkey' => 11702, 'permname' => 'Write Student Leads'],
        ['permkey' => 11703, 'permname' => 'Manage Student Leads'],
        ['permkey' => 11704, 'permname' => 'Read Enquiries'],
        ['permkey' => 11705, 'permname' => 'Write Enquiries'],
        ['permkey' => 11706, 'permname' => 'Manage Enquiries'],
        ['permkey' => 11707, 'permname' => 'Manage Registration Control'],
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->permissions as $permission) {
            DB::table('permission')->updateOrInsert(
                ['permkey' => $permission['permkey']],
                [
                    'permname' => $permission['permname'],
                    'created_at' => now(),
                ]
            );

            $perm = DB::table('permission')->where('permkey', $permission['permkey'])->first();
            $role = DB::table('role')->where('role_id', 1)->first();

            if ($role && $perm) {
                DB::table('rolepermission')->updateOrInsert([
                    'role_id' => $role->role_id,
                    'permission_id' => $perm->permission_id,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $permKeys = array_column($this->permissions, 'permkey');

        $permissionIds = DB::table('permission')
            ->whereIn('permkey', $permKeys)
            ->pluck('permission_id');

        DB::table('rolepermission')->whereIn('permission_id', $permissionIds)->delete();
        DB::table('permission')->whereIn('permission_id', $permissionIds)->delete();
    }
};

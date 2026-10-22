<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Lead Management was a single permkey (10201) that granted read, write and
 * delete at once. Split it into the Read/Write/Manage triple every other module
 * uses so a role can be given read-only access to leads.
 *
 * Roles that already hold 10201 had full access, so they are granted the two new
 * keys to keep today's behaviour. Revoke them from a role to make it read-only.
 */
return new class extends Migration
{
    private array $permissions = [
        ['permkey' => 10202, 'permname' => 'Write Leads'],
        ['permkey' => 10203, 'permname' => 'Manage Leads'],
    ];

    public function up(): void
    {
        // 10201 now means read only; rename it to match the other modules.
        DB::table('permission')->where('permkey', 10201)->update(['permname' => 'Read Leads']);

        foreach ($this->permissions as $permission) {
            DB::table('permission')->updateOrInsert(
                ['permkey' => $permission['permkey']],
                ['permname' => $permission['permname'], 'created_at' => now()]
            );
        }

        $newIds = DB::table('permission')
            ->whereIn('permkey', array_column($this->permissions, 'permkey'))
            ->pluck('permission_id');

        $readId = DB::table('permission')->where('permkey', 10201)->value('permission_id');

        if (!$readId) {
            return;
        }

        // Every role that could already manage leads keeps that ability.
        $roleIds = DB::table('rolepermission')->where('permission_id', $readId)->pluck('role_id');

        foreach ($roleIds as $roleId) {
            foreach ($newIds as $permissionId) {
                DB::table('rolepermission')->updateOrInsert([
                    'role_id' => $roleId,
                    'permission_id' => $permissionId,
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('permission')->where('permkey', 10201)->update(['permname' => 'Lead Management']);

        $permissionIds = DB::table('permission')
            ->whereIn('permkey', array_column($this->permissions, 'permkey'))
            ->pluck('permission_id');

        DB::table('rolepermission')->whereIn('permission_id', $permissionIds)->delete();
        DB::table('permission')->whereIn('permission_id', $permissionIds)->delete();
    }
};

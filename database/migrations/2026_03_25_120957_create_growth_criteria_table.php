<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('growth_criteria')) {
            Schema::create('growth_criteria', function (Blueprint $table) {
                $table->id('criterion_id');
                $table->string('criterion_name');
                $table->boolean('status')->default(1);
                $table->integer('display_order')->default(0);
                $table->timestamps();
            });

            // Seed initial criteria
            $criteria = [
                'Discipline',
                'Leadership',
                'Communication',
                'Teamwork',
                'Participation',
                'Behaviour'
            ];

            foreach ($criteria as $index => $name) {
                DB::table('growth_criteria')->updateOrInsert(
                    ['criterion_name' => $name],
                    [
                        'status' => 1,
                        'display_order' => $index + 1,
                        'updated_at' => now(),
                    ]
                );
            }
        }

        // Add Permissions
        $permissions = [
            ['permkey' => 10504, 'permname' => 'Manage Growth Criteria'], // Management UI access
        ];

        foreach ($permissions as $permission) {
            $existingPermission = DB::table('permission')->where('permkey', $permission['permkey'])->first();
            
            if ($existingPermission) {
                $permId = $existingPermission->permission_id;
            } else {
                $permId = DB::table('permission')->insertGetId([
                    'permkey' => $permission['permkey'],
                    'permname' => $permission['permname'],
                    'created_at' => now(),
                ]);
            }

            // Assign to Super Admin (1) and Initiator (2)
            foreach ([1, 2] as $roleId) {
                if (DB::table('role')->where('role_id', $roleId)->exists()) {
                    DB::table('rolepermission')->updateOrInsert([
                        'role_id' => $roleId,
                        'permission_id' => $permId,
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('growth_criteria');
        
        $permKeys = [10504];
        if (Schema::hasTable('permission')) {
            $permissionIds = DB::table('permission')
                ->whereIn('permkey', $permKeys)
                ->pluck('permission_id');

            if (Schema::hasTable('rolepermission')) {
                DB::table('rolepermission')->whereIn('permission_id', $permissionIds)->delete();
            }
            DB::table('permission')->whereIn('permission_id', $permissionIds)->delete();
        }
    }
};


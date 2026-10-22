<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('student_growth_records', function (Blueprint $table) {
            $table->id('growth_id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('school_id');
            $table->unsignedInteger('discipline')->default(0);
            $table->unsignedInteger('leadership')->default(0);
            $table->unsignedInteger('communication')->default(0);
            $table->unsignedInteger('teamwork')->default(0);
            $table->unsignedInteger('participation')->default(0);
            $table->unsignedInteger('behaviour')->default(0);
            $table->unsignedBigInteger('created_by');
            $table->date('entry_date');
            $table->timestamps();

            $table->foreign('student_id')->references('student_id')->on('student')->onDelete('cascade');
            $table->foreign('school_id')->references('school_id')->on('school')->onDelete('cascade');
            $table->foreign('created_by')->references('sysuser_id')->on('sysuser')->onDelete('cascade');
        });

        // Add Permissions
        $permissions = [
            ['permkey' => 10501, 'permname' => 'Read Growth Card'],
            ['permkey' => 10502, 'permname' => 'Write Growth Card'],
            ['permkey' => 10503, 'permname' => 'Manage Growth Card'],
        ];

        foreach ($permissions as $permission) {
            DB::table('permission')->updateOrInsert(
                ['permkey' => $permission['permkey']],
                [
                    'permname' => $permission['permname'],
                    'created_at' => now(),
                ]
            );

            $perm = DB::table('permission')->where('permkey', $permission['permkey'])->first();

            // Assign to Super Admin (1) and Initiator (2)
            foreach ([1, 2] as $roleId) {
                $role = DB::table('role')->where('role_id', $roleId)->first();
                if ($role && $perm) {
                    DB::table('rolepermission')->updateOrInsert([
                        'role_id' => $role->role_id,
                        'permission_id' => $perm->permission_id,
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
        $permKeys = [10501, 10502, 10503];
        $permissionIds = DB::table('permission')
            ->whereIn('permkey', $permKeys)
            ->pluck('permission_id');

        DB::table('rolepermission')->whereIn('permission_id', $permissionIds)->delete();
        DB::table('permission')->whereIn('permission_id', $permissionIds)->delete();
        
        Schema::dropIfExists('student_growth_records');
    }
};

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
        // 1. Add priority field to tickets table
        if (Schema::hasTable('tickets') && !Schema::hasColumn('tickets', 'priority')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->enum('priority', ['Low', 'Medium', 'High', 'Critical'])->default('Medium')->after('status');
            });
        }

        // 2. Create ticket_comments table
        if (!Schema::hasTable('ticket_comments')) {
            Schema::create('ticket_comments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('ticket_id');
                $table->unsignedBigInteger('user_id');
                $table->text('comment');
                $table->string('attachment')->nullable();
                $table->timestamps();

                $table->foreign('ticket_id')->references('ticket_id')->on('tickets')->onDelete('cascade');
                $table->foreign('user_id')->references('sysuser_id')->on('sysuser')->onDelete('cascade');
            });
        }

        // 3. Ensure 'Developer' role exists in the role table
        $roleId = DB::table('role')->where('rolename', 'Developer')->value('role_id');
        if (!$roleId) {
            $roleId = DB::table('role')->insertGetId([
                'rolename' => 'Developer',
                'ispredefined' => true,
                'created_at' => now(),
            ]);
        }

        // 4. Ensure Developer role has Read Tickets and Manage Tickets permissions
        $permissions = DB::table('permission')->whereIn('permkey', [10801, 10803])->get();
        foreach ($permissions as $permission) {
            DB::table('rolepermission')->updateOrInsert(
                [
                    'role_id' => $roleId,
                    'permission_id' => $permission->permission_id
                ],
                [
                    'role_id' => $roleId,
                    'permission_id' => $permission->permission_id
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_comments');

        if (Schema::hasTable('tickets') && Schema::hasColumn('tickets', 'priority')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->dropColumn('priority');
            });
        }

        // We do not drop the Developer role or permissions as it might delete users.
    }
};

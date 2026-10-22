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
        if (!Schema::hasTable('guardian_message')) {
            Schema::create('guardian_message', function (Blueprint $table) {
                $table->id();
                $table->string('subject');
                $table->text('message')->nullable();
                $table->enum('status', ['READY', 'PENDING', 'DONE'])->default('READY');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('guardian_message_queue')) {
            Schema::create('guardian_message_queue', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->comment('Refers to guardian_id');
                $table->unsignedBigInteger('guardian_message_id');
                $table->enum('status', ['PENDING', 'SENT'])->default('PENDING');
                $table->timestamps();

                $table->foreign('guardian_message_id')->references('id')->on('guardian_message')->onDelete('cascade');
                // Assuming user_id refers to guardian_id as per guardian table.
                $table->foreign('user_id')->references('guardian_id')->on('guardian')->onDelete('cascade');
            });
        }

        // Add permissions
        $permissions = [
            ['permkey' => 10211, 'permname' => 'Create Guardian Message'],
            ['permkey' => 10212, 'permname' => 'Send Guardian Message'],
            ['permkey' => 10213, 'permname' => 'View Guardian Message'],
        ];

        // Try to find the Admin role (role_id 2 based on current DB state)
        $role = DB::table('role')->where('rolename', 'Admin')->first()
                ?? DB::table('role')->where('role_id', 2)->first();

        foreach ($permissions as $permission) {
            DB::table('permission')->updateOrInsert(
                ['permkey' => $permission['permkey']],
                [
                    'permname' => $permission['permname'],
                    'created_at' => now(),
                ]
            );

            $perm = DB::table('permission')->where('permkey', $permission['permkey'])->first();

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
        $permKeys = [10211, 10212, 10213];
        $permissionIds = DB::table('permission')->whereIn('permkey', $permKeys)->pluck('permission_id');

        DB::table('rolepermission')->whereIn('permission_id', $permissionIds)->delete();
        DB::table('permission')->whereIn('permkey', $permKeys)->delete();

        Schema::dropIfExists('guardian_message_queue');
        Schema::dropIfExists('guardian_message');
    }
};

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
        // Global Templates for future usage as requested
        Schema::create('global_templates', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('content');
            $table->enum('type', ['Email', 'WhatsApp', 'Both'])->default('Email');
            $table->json('filters')->nullable()->comment('Default filters (School, District, etc.) for this template');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Student Announcements (Saved Configurations)
        Schema::create('student_announcements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('template_id')->nullable();
            $table->json('filters')->nullable()->comment('School, District, Language, Initiator filters');
            $table->string('subject')->nullable()->comment('Used for Email');
            $table->longText('message')->comment('Actual message sent (possibly edited)');
            $table->enum('communication_type', ['Email', 'WhatsApp']);
            $table->enum('status', ['READY', 'PENDING', 'DONE'])->default('READY');
            $table->string('created_by')->nullable();
            $table->timestamps();

            $table->foreign('template_id')->references('id')->on('global_templates')->onDelete('set null');
        });

        // Queue for sending to specific students
        Schema::create('student_announcement_queue', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('announcement_id');
            $table->unsignedBigInteger('student_id');
            $table->enum('status', ['PENDING', 'SENT', 'FAILED'])->default('PENDING');
            $table->timestamp('sent_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->foreign('announcement_id')->references('id')->on('student_announcements')->onDelete('cascade');
            $table->foreign('student_id')->references('student_id')->on('student')->onDelete('cascade');
        });

        // Add permissions for Announcement Module
        $permissions = [
            ['permkey' => 10221, 'permname' => 'Create Student Announcement'],
            ['permkey' => 10222, 'permname' => 'View Student Announcement History'],
            ['permkey' => 10223, 'permname' => 'Manage Announcement Templates'],
        ];

        foreach ($permissions as $permission) {
            $permissionId = DB::table('permission')->insertGetId([
                'permkey' => $permission['permkey'],
                'permname' => $permission['permname'],
                'created_at' => now(),
            ]);

            // Assign to Admin role (assuming role_id 2 is Admin as per guardian migration)
            $adminRole = DB::table('role')->where('role_id', 2)->first();
            if ($adminRole) {
                DB::table('rolepermission')->insert([
                    'role_id' => 2,
                    'permission_id' => $permissionId,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $permKeys = [10221, 10222, 10223];
        $permissionIds = DB::table('permission')->whereIn('permkey', $permKeys)->pluck('permission_id');
        
        DB::table('rolepermission')->whereIn('permission_id', $permissionIds)->delete();
        DB::table('permission')->whereIn('permkey', $permKeys)->delete();

        Schema::dropIfExists('student_announcement_queue');
        Schema::dropIfExists('student_announcements');
        Schema::dropIfExists('global_templates');
    }
};

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
        Schema::table('event', function (Blueprint $table) {
            $table->unsignedBigInteger('school_id')->nullable()->change();
            
            // Notification settings
            $table->string('communication_type')->default('Notification')->comment('Email, Notification, Both');
            $table->string('send_to')->default('All')->comment('All, Schools, Students, Guardians');
            
            // Area-wise filtering
            $table->unsignedBigInteger('state_id')->nullable();
            $table->unsignedBigInteger('dist_id')->nullable();
            $table->unsignedBigInteger('taluka_id')->nullable();
            
            // Existing column additions
            $table->foreign('state_id')->references('state_id')->on('ms_state')->onDelete('set null');
            $table->foreign('dist_id')->references('dist_id')->on('ms_dist')->onDelete('set null');
            $table->foreign('taluka_id')->references('takula_id')->on('ms_taluko')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event', function (Blueprint $table) {
            $table->dropForeign(['state_id']);
            $table->dropForeign(['dist_id']);
            $table->dropForeign(['taluka_id']);
            
            $table->dropColumn(['communication_type', 'send_to', 'state_id', 'dist_id', 'taluka_id']);
            $table->unsignedBigInteger('school_id')->nullable(false)->change();
        });
    }
};

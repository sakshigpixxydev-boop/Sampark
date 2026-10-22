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
        Schema::table('student', function (Blueprint $table) {
            $table->unsignedBigInteger('state_id')->nullable()->after('std_id');
            $table->unsignedBigInteger('dist_id')->nullable()->after('state_id');
            
            // Adding foreign keys
            $table->foreign('state_id')->references('state_id')->on('ms_state')->nullOnDelete();
            $table->foreign('dist_id')->references('dist_id')->on('ms_dist')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student', function (Blueprint $table) {
            $table->dropForeign(['state_id']);
            $table->dropForeign(['dist_id']);
            $table->dropColumn(['state_id', 'dist_id']);
        });
    }
};

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
        Schema::table('fee_plan', function (Blueprint $table) {
            $table->string('edmingle_course_id')->nullable()->after('is_active_on_form');
            $table->string('edmingle_batch_id')->nullable()->after('edmingle_course_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fee_plan', function (Blueprint $table) {
            $table->dropColumn(['edmingle_course_id', 'edmingle_batch_id']);
        });
    }
};

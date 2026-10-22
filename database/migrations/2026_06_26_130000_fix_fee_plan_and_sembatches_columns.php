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
        // Drop foreign keys if they exist (run in separate try-catch blocks to handle DB query exceptions)
        try {
            Schema::table('fee_plan', function (Blueprint $table) {
                $table->dropForeign('fee_plan_school_id_foreign');
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('fee_plan', function (Blueprint $table) {
                $table->dropForeign('fee_plan_std_id_foreign');
            });
        } catch (\Exception $e) {}

        // Drop unique key if it exists
        try {
            Schema::table('fee_plan', function (Blueprint $table) {
                $table->dropUnique('unq_fee_scope');
            });
        } catch (\Exception $e) {}

        // 1. Update fee_plan table
        Schema::table('fee_plan', function (Blueprint $table) {
            // Add plan_auto_close_date if it does not exist
            if (!Schema::hasColumn('fee_plan', 'plan_auto_close_date')) {
                $table->date('plan_auto_close_date')->nullable()->after('valid_to');
            }

            // Drop old columns if they exist
            if (Schema::hasColumn('fee_plan', 'school_id')) {
                $table->dropColumn('school_id');
            }
            if (Schema::hasColumn('fee_plan', 'std_id')) {
                $table->dropColumn('std_id');
            }
            if (Schema::hasColumn('fee_plan', 'edmingle_course_id')) {
                $table->dropColumn('edmingle_course_id');
            }
            if (Schema::hasColumn('fee_plan', 'edmingle_batch_id')) {
                $table->dropColumn('edmingle_batch_id');
            }
        });

        // 2. Update sembatches table
        Schema::table('sembatches', function (Blueprint $table) {
            // Drop old columns if they exist
            if (Schema::hasColumn('sembatches', 'std_id')) {
                $table->dropColumn('std_id');
            }
            if (Schema::hasColumn('sembatches', 'is_school_batch')) {
                $table->dropColumn('is_school_batch');
            }
            if (Schema::hasColumn('sembatches', 'sort_order')) {
                $table->dropColumn('sort_order');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Down not required as this fixes an inconsistent state
    }
};

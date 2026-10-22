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
        // 1. Update school table
        Schema::table('school', function (Blueprint $table) {
            if (!Schema::hasColumn('school', 'reg_token')) {
                $table->string('reg_token')->nullable()->unique()->after('status');
            }
            if (!Schema::hasColumn('school', 'reg_form_name')) {
                $table->string('reg_form_name')->nullable()->after('reg_token');
            }
            if (!Schema::hasColumn('school', 'reg_start_date')) {
                $table->dateTime('reg_start_date')->nullable()->after('reg_form_name');
            }
            if (!Schema::hasColumn('school', 'reg_end_date')) {
                $table->dateTime('reg_end_date')->nullable()->after('reg_start_date');
            }
            if (!Schema::hasColumn('school', 'notes')) {
                $table->text('notes')->nullable()->after('reg_end_date');
            }
        });

        // 2. Update student table
        Schema::table('student', function (Blueprint $table) {
            if (!Schema::hasColumn('student', 'registered_via_school_link')) {
                $table->boolean('registered_via_school_link')->default(false)->after('fees_status');
            }
            if (!Schema::hasColumn('student', 'school_approval_status')) {
                $table->string('school_approval_status')->default('Pending')->after('registered_via_school_link');
            }
            if (!Schema::hasColumn('student', 'batch_id')) {
                $table->unsignedBigInteger('batch_id')->nullable()->after('school_approval_status');
            }
        });

        try {
            Schema::table('student', function (Blueprint $table) {
                if (Schema::hasColumn('student', 'batch_id')) {
                    $table->foreign('batch_id')->references('batch_id')->on('upload_batch')->onDelete('set null');
                }
            });
        } catch (\Exception $e) {}

        // 3. Update fee_plan table
        Schema::table('fee_plan', function (Blueprint $table) {
            if (!Schema::hasColumn('fee_plan', 'school_id')) {
                $table->unsignedBigInteger('school_id')->nullable()->after('is_active_on_form');
            }
        });

        try {
            Schema::table('fee_plan', function (Blueprint $table) {
                if (Schema::hasColumn('fee_plan', 'school_id')) {
                    $table->foreign('school_id')->references('school_id')->on('school')->onDelete('set null');
                }
            });
        } catch (\Exception $e) {}
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('school', function (Blueprint $table) {
            $columnsToDrop = [];
            foreach (['reg_token', 'reg_form_name', 'reg_start_date', 'reg_end_date', 'notes'] as $col) {
                if (Schema::hasColumn('school', $col)) {
                    $columnsToDrop[] = $col;
                }
            }
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });

        try {
            Schema::table('student', function (Blueprint $table) {
                $table->dropForeign(['batch_id']);
            });
        } catch (\Exception $e) {}

        Schema::table('student', function (Blueprint $table) {
            $columnsToDrop = [];
            foreach (['registered_via_school_link', 'school_approval_status', 'batch_id'] as $col) {
                if (Schema::hasColumn('student', $col)) {
                    $columnsToDrop[] = $col;
                }
            }
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });

        try {
            Schema::table('fee_plan', function (Blueprint $table) {
                $table->dropForeign(['school_id']);
            });
        } catch (\Exception $e) {}

        Schema::table('fee_plan', function (Blueprint $table) {
            if (Schema::hasColumn('fee_plan', 'school_id')) {
                $table->dropColumn(['school_id']);
            }
        });
    }
};

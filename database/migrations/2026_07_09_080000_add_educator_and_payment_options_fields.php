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
        // 1. Add payment and settings columns to school table
        Schema::table('school', function (Blueprint $table) {
            if (!Schema::hasColumn('school', 'custom_fees')) {
                $table->text('custom_fees')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('school', 'services_features')) {
                $table->text('services_features')->nullable()->after('custom_fees');
            }
            if (!Schema::hasColumn('school', 'offline_payment_name')) {
                $table->string('offline_payment_name')->nullable()->after('services_features');
            }
            if (!Schema::hasColumn('school', 'offline_payment_contact')) {
                $table->string('offline_payment_contact')->nullable()->after('offline_payment_name');
            }
            if (!Schema::hasColumn('school', 'qr_code_path')) {
                $table->string('qr_code_path')->nullable()->after('offline_payment_contact');
            }
        });

        // 2. Add verification and payment columns to student table
        Schema::table('student', function (Blueprint $table) {
            if (!Schema::hasColumn('student', 'school_payment_method')) {
                $table->string('school_payment_method')->nullable()->after('school_approval_status');
            }
            if (!Schema::hasColumn('student', 'school_payment_screenshot')) {
                $table->string('school_payment_screenshot')->nullable()->after('school_payment_method');
            }
            if (!Schema::hasColumn('student', 'school_payment_upi_id')) {
                $table->string('school_payment_upi_id')->nullable()->after('school_payment_screenshot');
            }
            if (!Schema::hasColumn('student', 'school_payment_status')) {
                $table->string('school_payment_status')->default('Pending')->after('school_payment_upi_id');
            }
            if (!Schema::hasColumn('student', 'school_payment_verified_by')) {
                $table->unsignedBigInteger('school_payment_verified_by')->nullable()->after('school_payment_status');
            }
            if (!Schema::hasColumn('student', 'school_payment_verified_at')) {
                $table->timestamp('school_payment_verified_at')->nullable()->after('school_payment_verified_by');
            }
            if (!Schema::hasColumn('student', 'school_payment_rejection_reason')) {
                $table->string('school_payment_rejection_reason')->nullable()->after('school_payment_verified_at');
            }

            try {
                $table->foreign('school_payment_verified_by')->references('sysuser_id')->on('sysuser')->onDelete('set null');
            } catch (\Exception $e) {}
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student', function (Blueprint $table) {
            try {
                $table->dropForeign(['school_payment_verified_by']);
            } catch (\Exception $e) {}

            $cols = [
                'school_payment_method',
                'school_payment_screenshot',
                'school_payment_upi_id',
                'school_payment_status',
                'school_payment_verified_by',
                'school_payment_verified_at',
                'school_payment_rejection_reason'
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('student', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('school', function (Blueprint $table) {
            $cols = [
                'custom_fees',
                'services_features',
                'offline_payment_name',
                'offline_payment_contact',
                'qr_code_path'
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('school', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};


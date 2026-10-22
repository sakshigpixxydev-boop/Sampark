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
            if (!Schema::hasColumn('student', 'school_payment_plan_name')) {
                $table->string('school_payment_plan_name')->nullable()->after('school_payment_method');
            }
            if (!Schema::hasColumn('student', 'school_payment_amount')) {
                $table->decimal('school_payment_amount', 10, 2)->nullable()->after('school_payment_plan_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student', function (Blueprint $table) {
            foreach (['school_payment_plan_name', 'school_payment_amount'] as $col) {
                if (Schema::hasColumn('student', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

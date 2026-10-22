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
        Schema::table('initiator_financial_configs', function (Blueprint $table) {
            $table->decimal('new_school_registration_commission', 10, 2)->default(0)->change();
            $table->integer('school_count_base')->default(0)->change();
            $table->decimal('school_count_slab_amount', 10, 2)->default(0)->change();
            $table->decimal('per_student_commission_first_semester', 10, 2)->default(0)->change();
            $table->decimal('per_student_commission_repeat_semester', 10, 2)->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('initiator_financial_configs', function (Blueprint $table) {
            // Revert back or leave it be.
        });
    }
};

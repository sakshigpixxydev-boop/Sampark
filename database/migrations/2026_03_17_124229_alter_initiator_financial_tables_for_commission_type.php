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
            $table->tinyInteger('student_calculation_type')->default(0)->after('sysuser_id')->comment('0: per_student, 1: slab_based');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('initiator_financial_configs', function (Blueprint $table) {
            $table->dropColumn('student_calculation_type');
        });
    }
};

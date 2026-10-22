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
        // Add financial_type to sysuser table
        Schema::table('sysuser', function (Blueprint $table) {
            $table->string('financial_type', 20)->default('volunteer')->after('role_id')->comment('volunteer, paid');
        });

        // Create initiator_financial_configs table
        Schema::create('initiator_financial_configs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sysuser_id');
            $table->decimal('incentive_percentage', 10, 2)->default(0);
            $table->decimal('plan_based_percentage', 10, 2)->default(0);
            $table->decimal('new_school_registration_commission', 10, 2)->default(5000);
            $table->integer('school_count_base')->default(10);
            $table->decimal('school_count_slab_amount', 10, 2)->default(6000);
            $table->decimal('per_student_commission_first_semester', 10, 2)->default(20);
            $table->decimal('per_student_commission_repeat_semester', 10, 2)->default(10);
            $table->timestamps();

            $table->foreign('sysuser_id')->references('sysuser_id')->on('sysuser')->onDelete('cascade');
        });

        // Create initiator_financial_slabs table
        Schema::create('initiator_financial_slabs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('config_id');
            $table->integer('min_students');
            $table->integer('max_students')->nullable();
            $table->decimal('commission_percentage', 10, 2)->default(0);
            $table->decimal('fixed_amount', 10, 2)->default(0);
            $table->timestamps();

            $table->foreign('config_id')->references('id')->on('initiator_financial_configs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sysuser_and_create_initiator_financial_tables', function (Blueprint $table) {
            //
        });
    }
};

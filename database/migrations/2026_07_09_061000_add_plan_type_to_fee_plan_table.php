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
        if (!Schema::hasColumn('fee_plan', 'plan_type')) {
            Schema::table('fee_plan', function (Blueprint $table) {
                $table->string('plan_type')->default('student')->after('fee_plan_id');
            });
        }

        Schema::create('fee_plan_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sysuser_id');
            $table->unsignedBigInteger('fee_plan_id');
            $table->timestamps();

            $table->foreign('sysuser_id')->references('sysuser_id')->on('sysuser')->onDelete('cascade');
            $table->foreign('fee_plan_id')->references('fee_plan_id')->on('fee_plan')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_plan_user');

        if (Schema::hasColumn('fee_plan', 'plan_type')) {
            Schema::table('fee_plan', function (Blueprint $table) {
                $table->dropColumn('plan_type');
            });
        }
    }
};

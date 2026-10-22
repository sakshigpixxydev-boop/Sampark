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
            $table->boolean('is_active_on_form')->default(1)->after('std_id')->comment('1=Active, 0=Inactive');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fee_plan', function (Blueprint $table) {
            $table->dropColumn('is_active_on_form');
        });
    }
};

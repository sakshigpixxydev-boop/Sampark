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
        Schema::table('upload_batch', function (Blueprint $table) {
            $table->decimal('paid_amount', 12, 2)->default(0.00)->after('total_fees');
        });

        Schema::table('pending_student', function (Blueprint $table) {
            $table->boolean('is_registered')->default(false)->after('validation_errors');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('upload_batch', function (Blueprint $table) {
            $table->dropColumn('paid_amount');
        });

        Schema::table('pending_student', function (Blueprint $table) {
            $table->dropColumn('is_registered');
        });
    }
};

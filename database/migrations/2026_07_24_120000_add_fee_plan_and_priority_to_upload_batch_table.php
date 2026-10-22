<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('upload_batch', function (Blueprint $table) {
            if (!Schema::hasColumn('upload_batch', 'fee_plan_id')) {
                $table->unsignedBigInteger('fee_plan_id')->nullable();
                try {
                    $table->foreign('fee_plan_id')->references('fee_plan_id')->on('fee_plan')->onDelete('set null');
                } catch (\Throwable $e) {}
            }
        });
    }

    public function down(): void
    {
        try {
            Schema::table('upload_batch', function (Blueprint $table) {
                $table->dropForeign(['fee_plan_id']);
            });
        } catch (\Throwable $e) {}

        try {
            Schema::table('upload_batch', function (Blueprint $table) {
                if (Schema::hasColumn('upload_batch', 'priority')) {
                    $table->dropColumn('priority');
                }
                if (Schema::hasColumn('upload_batch', 'fee_plan_id')) {
                    $table->dropColumn('fee_plan_id');
                }
            });
        } catch (\Throwable $e) {}
    }
};

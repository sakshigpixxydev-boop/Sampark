<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('upload_batch', function (Blueprint $table) {
            if (Schema::hasColumn('upload_batch', 'payment_mode_id')) {
                $table->dropForeign(['payment_mode_id']);
                $table->dropColumn('payment_mode_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('upload_batch', function (Blueprint $table) {
            if (!Schema::hasColumn('upload_batch', 'payment_mode_id')) {
                $table->unsignedBigInteger('payment_mode_id')->nullable()->after('payment_verified');
                $table->foreign('payment_mode_id')->references('paymode_id')->on('paymentmode')->onDelete('set null');
            }
        });
    }
};

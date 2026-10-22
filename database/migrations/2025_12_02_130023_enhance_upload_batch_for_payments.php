<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('upload_batch', 'school_id')) {
            Schema::table('upload_batch', function (Blueprint $table) {
                $table->unsignedBigInteger('school_id')->nullable()->after('sembatches_id');
                $table->enum('payment_type', ['online', 'offline'])->nullable()->after('payment_mode_id');
                $table->enum('offline_payment_method', ['bank_transfer', 'cheque', 'other'])->nullable()->after('payment_type');
                $table->string('bank_name', 255)->nullable()->after('offline_payment_method');
                $table->string('razorpay_order_id', 100)->nullable()->after('bank_name');
                $table->string('razorpay_payment_id', 100)->nullable()->after('razorpay_order_id');
                $table->string('razorpay_signature', 255)->nullable()->after('razorpay_payment_id');

                $table->foreign('school_id')->references('school_id')->on('school')->onDelete('cascade');

                $table->index('payment_type');
                $table->index('razorpay_order_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('upload_batch', function (Blueprint $table) {
            $table->dropForeign(['school_id']);
            $table->dropColumn([
                'school_id',
                'payment_type',
                'offline_payment_method',
                'bank_name',
                'razorpay_order_id',
                'razorpay_payment_id',
                'razorpay_signature'
            ]);
        });
    }
};

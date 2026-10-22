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
        Schema::create('paymentlog', function (Blueprint $table) {
            $table->id('payment_id');
            $table->unsignedBigInteger('paymode_id');
            $table->dateTime('processdatetime');
            $table->decimal('amount', 10, 2)->nullable();
            $table->char('status', 1)->nullable()->comment('0 - PENDING, 1 - AUTHORIZED, 2 - REFUNDED, 3 - FAILED, 4 - CANCELLED');
            $table->string('txnid', 100)->comment('transaction reference id');
            $table->longText('orgresponse')->nullable()->comment('store whole response');
            $table->decimal('discount', 8, 2)->nullable();
            $table->unsignedBigInteger('invoice_id');
            
            $table->foreign('paymode_id')->references('paymode_id')->on('paymentmode')->onDelete('cascade');
            $table->foreign('invoice_id')->references('invoice_id')->on('invoice')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paymentlog');
    }
};

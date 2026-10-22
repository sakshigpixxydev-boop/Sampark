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
        Schema::create('processed_razorpay_events', function (Blueprint $table) {
            $table->string('event_id')->primary(); // Unique Razorpay Event ID
            $table->string('payment_id')->nullable()->index(); // Razorpay Payment ID
            $table->string('order_id')->nullable()->index(); // Razorpay Order ID
            $table->string('event_type'); // e.g. payment.captured
            $table->json('payload')->nullable(); // Full payload for audit log
            $table->string('status')->default('processed'); // processed, duplicate, failed
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('processed_razorpay_events');
    }
};

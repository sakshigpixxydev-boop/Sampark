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
        Schema::create('upload_batch', function (Blueprint $table) {
            $table->id('batch_id');
            $table->string('batch_code', 50)->unique();
            $table->unsignedBigInteger('sembatches_id');
            $table->unsignedBigInteger('uploaded_by');
            $table->dateTime('upload_date');
            $table->integer('total_students');
            $table->decimal('fee_per_student', 10, 2);
            $table->decimal('total_fees', 12, 2);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->boolean('payment_verified')->default(false);
            $table->unsignedBigInteger('payment_mode_id')->nullable();
            $table->string('transaction_id', 100)->nullable();
            $table->dateTime('payment_date')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->dateTime('verified_at')->nullable();
            $table->text('notes')->nullable();
            
            $table->foreign('sembatches_id')->references('sembatches_id')->on('sembatches')->onDelete('cascade');
            $table->foreign('uploaded_by')->references('sysuser_id')->on('sysuser')->onDelete('cascade');
            $table->foreign('verified_by')->references('sysuser_id')->on('sysuser')->onDelete('set null');
            $table->foreign('payment_mode_id')->references('paymode_id')->on('paymentmode')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('upload_batch');
    }
};

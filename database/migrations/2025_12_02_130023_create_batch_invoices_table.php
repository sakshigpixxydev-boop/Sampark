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
        Schema::create('batch_invoices', function (Blueprint $table) {
            $table->id('invoice_id');
            $table->unsignedBigInteger('batch_id');
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('initiator_id')->nullable();
            $table->string('invoice_number', 50)->unique();
            $table->decimal('total_amount', 10, 2);
            $table->date('invoice_date');
            $table->boolean('exported')->default(false);
            $table->timestamp('exported_at')->nullable();
            $table->unsignedBigInteger('exported_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('batch_id')->references('batch_id')->on('upload_batch')->onDelete('cascade');
            $table->foreign('school_id')->references('school_id')->on('school')->onDelete('cascade');
            $table->foreign('initiator_id')->references('sysuser_id')->on('sysuser')->onDelete('set null');
            $table->foreign('exported_by')->references('sysuser_id')->on('sysuser')->onDelete('set null');

            $table->index('invoice_number');
            $table->index('invoice_date');
            $table->index('exported');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batch_invoices');
    }
};

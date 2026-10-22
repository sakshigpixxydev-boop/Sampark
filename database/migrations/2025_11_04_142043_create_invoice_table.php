<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoice', function (Blueprint $table) {
            $table->id('invoice_id');
            $table->date('invoice_date')->useCurrent();
            $table->date('due_date')->nullable();
            $table->string('notes', 500)->nullable();
            $table->decimal('total_before_discount', 12, 2)->default(0)->comment('subtotal');
            $table->decimal('discount_total', 12, 2)->default(0)->comment('percentage + flat all discount total');
            $table->decimal('total_due', 12, 2)->default(0)->comment('total_before_discount - discount_total');
            $table->enum('status', ['DRAFT', 'ISSUED', 'PARTIAL', 'PAID', 'VOID'])->default('ISSUED');
            $table->date('created_at')->useCurrent();
            $table->date('updated_at')->nullable();
            $table->unsignedBigInteger('school_id')->nullable();
            $table->unsignedBigInteger('student_id')->nullable();
            
            $table->foreign('school_id')->references('school_id')->on('school')->onDelete('cascade');
            $table->foreign('student_id')->references('student_id')->on('student')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice');
    }
};

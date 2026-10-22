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
        Schema::create('pending_student', function (Blueprint $table) {
            $table->id('pending_id');
            $table->unsignedBigInteger('batch_id');
            $table->json('student_data');
            $table->decimal('calculated_fee', 10, 2);
            $table->json('validation_errors')->nullable();
            $table->dateTime('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            
            $table->foreign('batch_id')->references('batch_id')->on('upload_batch')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pending_student');
    }
};

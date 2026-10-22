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
        Schema::create('certificates', function (Blueprint $table) {
            $table->id('certificate_id');
            $table->string('type'); // STUDENT, EVENT, PROGRAM
            $table->unsignedBigInteger('reference_id'); // Can be student_id or others
            $table->unsignedBigInteger('sembatches_id')->nullable();
            $table->string('certificate_no')->unique();
            $table->string('recipient_name');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->date('generated_date');
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};

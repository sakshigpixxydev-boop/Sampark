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
        Schema::create('student_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('from_sembatches_id')->nullable();
            $table->unsignedBigInteger('to_sembatches_id');
            $table->string('status', 50)->default('Migrated');
            $table->string('migrated_by', 100)->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('student_id')->references('student_id')->on('student')->onDelete('cascade');
            $table->foreign('from_sembatches_id')->references('sembatches_id')->on('sembatches')->onDelete('set null');
            $table->foreign('to_sembatches_id')->references('sembatches_id')->on('sembatches')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_history');
    }
};

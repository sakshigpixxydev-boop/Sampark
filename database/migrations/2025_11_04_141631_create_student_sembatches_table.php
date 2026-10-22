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
        Schema::create('student_sembatches', function (Blueprint $table) {
            $table->id('std_batch_id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('sembatches_id');
            $table->smallInteger('status')->comment('0 -active, 1-completed, 2-dropped, 3-on-hold');
            $table->timestamp('assigned_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('completed_at')->nullable();
            
            $table->foreign('student_id')->references('student_id')->on('student')->onDelete('cascade');
            $table->foreign('sembatches_id')->references('sembatches_id')->on('sembatches')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_sembatches');
    }
};

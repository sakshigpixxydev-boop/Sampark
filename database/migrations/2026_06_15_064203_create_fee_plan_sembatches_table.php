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
        Schema::create('fee_plan_sembatches', function (Blueprint $table) {
            $table->unsignedBigInteger('fee_plan_id');
            $table->unsignedBigInteger('sembatches_id');
            $table->primary(['fee_plan_id', 'sembatches_id']);
            
            $table->foreign('fee_plan_id')->references('fee_plan_id')->on('fee_plan')->onDelete('cascade');
            $table->foreign('sembatches_id')->references('sembatches_id')->on('sembatches')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_plan_sembatches');
    }
};

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
        Schema::create('invoice_item', function (Blueprint $table) {
            $table->id('invoice_item_id');
            $table->string('description', 255)->nullable();
            $table->decimal('qty', 10, 2)->nullable();
            $table->decimal('unit_amount', 12, 2)->nullable();
            $table->decimal('line_amount', 12, 2)->nullable();
            $table->unsignedBigInteger('invoice_id');
            $table->unsignedBigInteger('fee_plan_id')->nullable();
            
            $table->foreign('invoice_id')->references('invoice_id')->on('invoice')->onDelete('cascade');
            $table->foreign('fee_plan_id')->references('fee_plan_id')->on('fee_plan')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_item');
    }
};

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
        Schema::create('fee_plan', function (Blueprint $table) {
            $table->id('fee_plan_id');
            $table->string('fee_name', 200);
            $table->decimal('amount', 10, 2);
            $table->date('valid_from');
            $table->date('valid_to')->nullable();
            $table->date('created_at')->useCurrent();
            $table->date('updated_at')->nullable();
            $table->unsignedBigInteger('sembatches_id')->nullable();
            $table->unsignedBigInteger('school_id')->nullable();
            $table->unsignedBigInteger('std_id')->nullable();
            
            $table->unique(['school_id', 'std_id', 'sembatches_id', 'fee_name', 'valid_from'], 'unq_fee_scope');
            
            $table->foreign('sembatches_id')->references('sembatches_id')->on('sembatches')->onDelete('cascade');
            $table->foreign('school_id')->references('school_id')->on('school')->onDelete('cascade');
            $table->foreign('std_id')->references('std_id')->on('ms_standard')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_plan');
    }
};

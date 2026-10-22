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
        Schema::create('discount_rule', function (Blueprint $table) {
            $table->id('discount_id');
            $table->enum('scope', ['SCHOOL', 'STUDENT']);
            $table->enum('discount_type', ['FLAT', 'PERCENT']);
            $table->decimal('discount_value', 10, 2);
            $table->enum('applies_to', ['ALL', 'TUITION'])->default('ALL')->comment('ALL, TUITION, etc..');
            $table->date('valid_from');
            $table->date('valid_to')->nullable();
            $table->boolean('active')->default(true);
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
        Schema::dropIfExists('discount_rule');
    }
};

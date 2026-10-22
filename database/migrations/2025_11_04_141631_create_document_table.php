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
        Schema::create('document', function (Blueprint $table) {
            $table->id('doc_id');
            $table->unsignedBigInteger('student_id');
            $table->binary('photo')->nullable();
            $table->binary('result')->nullable();
            $table->longText('achievement')->nullable();
            $table->binary('satsang_verification')->nullable();
            $table->date('create_at')->useCurrent();
            $table->date('update_at')->nullable();
            
            $table->foreign('student_id')->references('student_id')->on('student')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document');
    }
};

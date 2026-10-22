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
        Schema::create('student_lang', function (Blueprint $table) {
            $table->id('stlg_id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('lg_id');
            $table->boolean('understand_lg')->default(false);
            $table->boolean('speak_lg')->default(false);
            $table->boolean('read_lg')->default(false);
            $table->boolean('write_lg')->default(false);
            $table->boolean('isprimary')->default(false);
            $table->string('notes', 200)->nullable();
            
            $table->foreign('student_id')->references('student_id')->on('student')->onDelete('cascade');
            $table->foreign('lg_id')->references('lg_id')->on('language')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_lang');
    }
};

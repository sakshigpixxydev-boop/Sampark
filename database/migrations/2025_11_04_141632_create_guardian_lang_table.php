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
        Schema::create('guardian_lang', function (Blueprint $table) {
            $table->id('gl_id');
            $table->unsignedBigInteger('lg_id');
            $table->unsignedBigInteger('guardian_id');
            $table->boolean('understand_lg')->default(false);
            $table->boolean('speak_lg')->default(false);
            $table->boolean('read_lg')->default(false);
            $table->boolean('write_lg')->default(false);
            $table->boolean('isprimary')->default(false);
            $table->string('notes', 200)->nullable();
            
            $table->foreign('lg_id')->references('lg_id')->on('language')->onDelete('cascade');
            $table->foreign('guardian_id')->references('guardian_id')->on('guardian')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guardian_lang');
    }
};

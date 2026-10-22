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
        Schema::create('userschool', function (Blueprint $table) {
            $table->id('userschool_id');
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('sysuser_id');
            
            $table->foreign('sysuser_id')->references('sysuser_id')->on('sysuser')->onDelete('cascade');
            // school_id foreign key will be added after school table is created
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('userschool');
    }
};

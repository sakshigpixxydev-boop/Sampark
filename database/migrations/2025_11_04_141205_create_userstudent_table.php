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
        Schema::create('userstudent', function (Blueprint $table) {
            $table->id('userstudent_id');
            $table->unsignedBigInteger('sysuser_id');
            $table->unsignedBigInteger('student_id');
            
            $table->foreign('sysuser_id')->references('sysuser_id')->on('sysuser')->onDelete('cascade');
            // student_id foreign key will be added after student table is created
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('userstudent');
    }
};

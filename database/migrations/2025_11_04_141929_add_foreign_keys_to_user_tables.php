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
        Schema::table('userschool', function (Blueprint $table) {
            $table->foreign('school_id')->references('school_id')->on('school')->onDelete('cascade');
        });
        
        Schema::table('userstudent', function (Blueprint $table) {
            $table->foreign('student_id')->references('student_id')->on('student')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('userschool', function (Blueprint $table) {
            $table->dropForeign(['school_id']);
        });
        
        Schema::table('userstudent', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
        });
    }
};

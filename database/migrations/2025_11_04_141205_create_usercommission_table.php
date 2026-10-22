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
        Schema::create('usercommission', function (Blueprint $table) {
            $table->id('usercmmission_id');
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('cbatch_id');
            $table->unsignedBigInteger('sysuser_id');
            $table->date('valid_from');
            $table->date('valid_to')->nullable();
            
            $table->foreign('sysuser_id')->references('sysuser_id')->on('sysuser')->onDelete('cascade');
            // school_id and cbatch_id foreign keys will be added after respective tables are created
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usercommission');
    }
};

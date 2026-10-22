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
        Schema::create('parcel', function (Blueprint $table) {
            $table->id('parcel_id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('parcelcom_id');
            $table->string('trakingnumber', 150);
            $table->string('notes', 500)->nullable();
            $table->date('create_at')->useCurrent();
            
            $table->foreign('student_id')->references('student_id')->on('student')->onDelete('cascade');
            $table->foreign('parcelcom_id')->references('parcelcom_id')->on('parcelcompany')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parcel');
    }
};

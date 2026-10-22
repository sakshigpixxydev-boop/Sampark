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
        Schema::create('student', function (Blueprint $table) {
            $table->id('student_id');
            $table->unsignedBigInteger('studenttype_id');
            $table->unsignedBigInteger('satsangmandal_id')->nullable();
            $table->unsignedBigInteger('shikharm_id')->nullable();
            $table->unsignedBigInteger('kshetra_id')->nullable();
            $table->unsignedBigInteger('eduboard_id');
            $table->unsignedBigInteger('std_id');
            $table->unsignedBigInteger('school_id')->nullable();
            $table->unsignedBigInteger('takula_id');
            $table->string('fname', 100);
            $table->string('mname', 100);
            $table->string('lname', 100);
            $table->string('gender', 10)->nullable()->comment('M - Male, F - Female, O - Other');
            $table->string('email', 255)->unique();
            $table->date('birthdate')->nullable();
            $table->string('address', 500)->nullable();
            $table->string('city_village', 50)->nullable();
            $table->string('pincode', 10)->nullable();
            $table->string('contact_no', 10)->nullable();
            $table->string('whatsapp_no', 10)->nullable();
            $table->enum('educationmedium', ['1', '2', '3', '4'])->nullable()->comment('1 - Gujarati, 2 - Hindi, 3 - English, 4 - Other');
            $table->string('institute', 500)->nullable();
            $table->text('achievement_stsang')->nullable();
            $table->text('achievement_academic')->nullable();
            $table->text('activities')->nullable();
            $table->enum('learnabout', ['1', '2', '3', '4'])->nullable()->comment('1- From BAPS Magazines, 2-From Social Media, 3- From Satsang Sabha, 4- From an Individual');
            $table->enum('materiallanguage', ['1', '2'])->nullable()->comment('1 - English, 2 - Hindi');
            $table->unsignedBigInteger('taluka_id_postal')->nullable();
            $table->unsignedBigInteger('city_postal')->nullable();
            $table->string('address_postal', 500)->nullable();
            $table->text('notes')->nullable();
            $table->string('refby', 100)->nullable();
            $table->string('createdby', 100)->nullable();
            $table->string('lmsuser', 255)->nullable();
            $table->string('lmspswd', 255)->nullable();
            
            $table->foreign('studenttype_id')->references('studenttype_id')->on('studenttype')->onDelete('cascade');
            $table->foreign('satsangmandal_id')->references('satsangmandal_id')->on('ms_satsangmandal')->onDelete('set null');
            $table->foreign('shikharm_id')->references('shikharm_id')->on('ms_shikharmandir')->onDelete('set null');
            $table->foreign('kshetra_id')->references('kshetra_id')->on('ms_kshetra')->onDelete('set null');
            $table->foreign('eduboard_id')->references('eduboard_id')->on('ms_eductionalboard')->onDelete('cascade');
            $table->foreign('std_id')->references('std_id')->on('ms_standard')->onDelete('cascade');
            $table->foreign('school_id')->references('school_id')->on('school')->onDelete('set null');
            $table->foreign('takula_id')->references('takula_id')->on('ms_taluko')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student');
    }
};

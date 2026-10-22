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
        Schema::create('school', function (Blueprint $table) {
            $table->id('school_id');
            $table->unsignedBigInteger('schooltype_id');
            $table->unsignedBigInteger('eduboard_id');
            $table->string('schoolname', 1000);
            $table->string('logourl', 1000)->nullable()->comment('URL or path to the school\'s logo (optional).');
            $table->string('address1', 255)->nullable();
            $table->string('address2', 255)->nullable();
            $table->string('city_village', 100)->nullable();
            $table->unsignedBigInteger('takula_id');
            $table->string('pincode', 10)->nullable();
            $table->string('phone', 15)->nullable();
            $table->string('mobile', 10)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('website', 255)->nullable();
            $table->integer('establishedyear')->nullable();
            $table->string('adminname', 200);
            $table->string('admincontact', 10);
            $table->boolean('inactive')->default(false);
            $table->date('created_at')->useCurrent();
            $table->date('updated_at')->nullable();
            $table->string('createdby', 100)->nullable();
            $table->smallInteger('status')->nullable()->comment('0-temp, 1-final');
            
            $table->foreign('schooltype_id')->references('schooltype_id')->on('schooltype')->onDelete('cascade');
            $table->foreign('eduboard_id')->references('eduboard_id')->on('ms_eductionalboard')->onDelete('cascade');
            $table->foreign('takula_id')->references('takula_id')->on('ms_taluko')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school');
    }
};

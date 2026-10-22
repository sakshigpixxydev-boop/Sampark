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
        Schema::create('sysuser', function (Blueprint $table) {
            $table->id('sysuser_id');
            $table->unsignedBigInteger('role_id');
            $table->string('fname', 100);
            $table->string('lname', 100)->nullable();
            $table->string('mname', 100)->nullable();
            $table->string('email', 250)->nullable();
            $table->string('phone', 10)->nullable();
            $table->string('address1', 250)->nullable();
            $table->string('address2', 250)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('pincode', 10)->nullable();
            $table->string('whatsapp', 10)->nullable();
            $table->string('pswd', 150);
            $table->string('pin', 4)->nullable()->comment('set 4 digit pin');
            $table->boolean('inactive')->default(false);
            $table->date('created_at')->useCurrent();
            $table->date('updated_at')->nullable();
            $table->string('createdby', 100)->nullable();
            $table->string('username', 255)->unique();
            $table->string('refcode', 100)->nullable();
            
            $table->foreign('role_id')->references('role_id')->on('role')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sysuser');
    }
};

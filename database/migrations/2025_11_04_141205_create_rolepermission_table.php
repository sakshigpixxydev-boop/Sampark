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
        Schema::create('rolepermission', function (Blueprint $table) {
            $table->id('rolepermission_id');
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('role_id');
            
            $table->foreign('permission_id')->references('permission_id')->on('permission')->onDelete('cascade');
            $table->foreign('role_id')->references('role_id')->on('role')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rolepermission');
    }
};

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
        Schema::create('user_reads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sysuser_id');
            $table->unsignedBigInteger('readable_id');
            $table->string('readable_type'); // Blog, Event, Announcement
            $table->timestamps();

            $table->foreign('sysuser_id')->references('sysuser_id')->on('sysuser')->onDelete('cascade');
            $table->index(['sysuser_id', 'readable_id', 'readable_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_reads');
    }
};

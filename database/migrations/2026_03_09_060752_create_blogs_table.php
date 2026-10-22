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
        Schema::create('blogs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->string('title');
            $table->longText('content')->nullable();
            $table->enum('status', ['Draft', 'Pending', 'Approved', 'Rejected'])->default('Draft');
            $table->unsignedBigInteger('reviewer_id')->nullable();
            $table->text('review_comment')->nullable();
            $table->timestamps();

            $table->foreign('student_id')->references('student_id')->on('student')->onDelete('cascade');
            $table->foreign('reviewer_id')->references('sysuser_id')->on('sysuser')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blogs');
    }
};

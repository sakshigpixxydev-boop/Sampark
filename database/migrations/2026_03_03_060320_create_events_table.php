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
        Schema::create('event', function (Blueprint $table) {
            $table->id('event_id');
            $table->unsignedBigInteger('school_id');
            $table->string('title', 255);
            $table->date('event_date');
            $table->text('notes')->nullable();
            $table->string('photo', 500)->nullable();
            $table->text('feedback')->nullable();
            $table->date('reminder_date')->nullable();
            $table->string('createdby', 100)->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->nullable()->useCurrentOnUpdate();

            $table->foreign('school_id')->references('school_id')->on('school')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event');
    }
};

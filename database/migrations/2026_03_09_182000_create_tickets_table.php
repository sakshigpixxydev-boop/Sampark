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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id('ticket_id');
            $table->unsignedBigInteger('school_id');
            $table->string('ticket_type'); // IT, Account, Student, Other
            $table->string('title');
            $table->text('description');
            $table->string('attachment')->nullable();
            $table->enum('status', ['Open', 'In Progress', 'Resolved', 'Closed'])->default('Open');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->timestamps();

            $table->foreign('school_id')->references('school_id')->on('school')->onDelete('cascade');
            $table->foreign('created_by')->references('sysuser_id')->on('sysuser')->onDelete('cascade');
            $table->foreign('assigned_to')->references('sysuser_id')->on('sysuser')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};

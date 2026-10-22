<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('school_leads', function (Blueprint $table) {
            $table->id('lead_id');
            $table->unsignedBigInteger('initiator_id');
            $table->string('school_name', 1000);
            $table->string('email', 150)->nullable();
            $table->string('phone', 15)->nullable();
            $table->integer('estimated_students')->nullable();
            $table->text('address')->nullable();
            $table->string('website', 255)->nullable();
            $table->string('registration_token', 12)->unique()->comment('Short unique token for registration URL');
            $table->timestamp('token_expires_at')->nullable()->comment('Token expiration date (default 30 days)');
            $table->timestamp('token_used_at')->nullable()->comment('When token was used');
            $table->enum('status', ['pending', 'registered', 'expired'])->default('pending');
            $table->unsignedBigInteger('school_id')->nullable()->comment('Reference to registered school');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('initiator_id')->references('sysuser_id')->on('sysuser')->onDelete('cascade');
            $table->foreign('school_id')->references('school_id')->on('school')->onDelete('set null');

            $table->index('registration_token');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_leads');
    }
};

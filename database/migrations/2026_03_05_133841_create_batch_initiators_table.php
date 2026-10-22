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
        Schema::create('batch_initiators', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('batch_id');
            $table->string('initiator_code', 100)->nullable();
            $table->string('first_name', 100)->nullable();
            $table->string('middle_name', 100)->nullable();
            $table->string('last_name', 100)->nullable();
            $table->string('gender', 20)->nullable();
            $table->string('whatsapp_contact', 20)->nullable();
            $table->string('email', 255)->nullable();
            $table->date('enroll_school_date')->nullable();
            $table->string('enroll_school_name', 255)->nullable();
            $table->text('school_address')->nullable();
            $table->string('geo_tagging_school_address', 500)->nullable();
            $table->string('school_contact_person', 255)->nullable();
            $table->string('position', 100)->nullable();
            $table->string('contact_no', 20)->nullable();
            $table->integer('total_enroll_student')->default(0);
            $table->decimal('payment_received', 12, 2)->default(0);
            $table->string('mode_of_payment', 100)->nullable();
            $table->decimal('payment_pending', 12, 2)->default(0);
            $table->decimal('honorarium', 12, 2)->default(0);
            $table->date('tentative_date_full_payment')->nullable();
            $table->timestamps();

            $table->foreign('batch_id')->references('batch_id')->on('upload_batch')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batch_initiators');
    }
};

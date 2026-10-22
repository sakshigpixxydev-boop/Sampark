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
        Schema::dropIfExists('batch_initiators');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Not easily reversible since the request is to remove it because it is no longer needed
        Schema::create('batch_initiators', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('batch_id');
            $table->string('initiator_code')->nullable();
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('gender')->nullable();
            $table->string('whatsapp_contact')->nullable();
            $table->string('email')->nullable();
            $table->date('enroll_school_date')->nullable();
            $table->string('enroll_school_name')->nullable();
            $table->text('school_address')->nullable();
            $table->text('geo_tagging_school_address')->nullable();
            $table->string('school_contact_person')->nullable();
            $table->string('position')->nullable();
            $table->string('contact_no')->nullable();
            $table->integer('total_enroll_student')->default(0);
            $table->decimal('payment_received', 10, 2)->default(0);
            $table->string('mode_of_payment')->nullable();
            $table->decimal('payment_pending', 10, 2)->default(0);
            $table->decimal('honorarium', 10, 2)->default(0);
            $table->date('tentative_date_full_payment')->nullable();
            $table->timestamps();
        });
    }
};

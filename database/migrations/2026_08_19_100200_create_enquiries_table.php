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
        if (Schema::hasTable('enquiries')) {
            return;
        }

        Schema::create('enquiries', function (Blueprint $table) {
            $table->bigIncrements('enquiry_id');
            $table->string('source', 50)->default('external_api');

            // Core contact
            $table->string('first_name', 100);
            $table->string('middle_name', 100)->nullable();
            $table->string('last_name', 100)->nullable();
            $table->string('email', 150)->nullable()->index();
            $table->string('phone', 20)->nullable()->index();
            $table->string('whatsapp_no', 20)->nullable();
            $table->char('gender', 1)->nullable();
            $table->date('birthdate')->nullable();

            // Academic details. Both the resolved master-data id and the text the
            // caller posted are kept: an external site does not know our ids.
            $table->unsignedInteger('std_id')->nullable();
            $table->string('standard_name', 100)->nullable();
            $table->unsignedInteger('eduboard_id')->nullable();
            $table->string('board_name', 100)->nullable();
            $table->string('medium', 50)->nullable();
            $table->string('stream', 20)->nullable();
            $table->string('school_name', 255)->nullable();

            // Location
            $table->unsignedInteger('country_id')->nullable();
            $table->unsignedInteger('state_id')->nullable();
            $table->unsignedInteger('dist_id')->nullable();
            $table->unsignedInteger('takula_id')->nullable();
            $table->string('country_name', 100)->nullable();
            $table->string('state_name', 100)->nullable();
            $table->string('district_name', 100)->nullable();
            $table->string('taluka_name', 100)->nullable();
            $table->string('city_village', 100)->nullable();
            $table->string('address', 500)->nullable();
            $table->string('pincode', 10)->nullable();

            // Guardian
            $table->string('guardian_name', 200)->nullable();
            $table->string('guardian_contact', 20)->nullable();
            $table->string('guardian_relation', 50)->nullable();

            $table->string('selected_language', 10)->default('en');
            $table->text('message')->nullable();

            // Workflow
            $table->string('status', 20)->default('New');
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('converted_student_id')->nullable();
            $table->timestamp('contacted_at')->nullable();

            // Audit / debugging
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->json('raw_payload')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enquiries');
    }
};

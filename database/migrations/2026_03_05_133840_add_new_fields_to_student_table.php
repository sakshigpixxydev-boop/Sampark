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
        Schema::table('student', function (Blueprint $table) {
            $table->string('school_educator_name', 255)->nullable()->after('institute');
            $table->string('school_educator_no', 20)->nullable()->after('school_educator_name');
            $table->string('geo_tagging_school_address', 500)->nullable()->after('school_educator_no');
            $table->string('initiator_contact', 20)->nullable()->after('geo_tagging_school_address');
            $table->string('application_status', 50)->nullable()->after('initiator_contact');
            $table->string('fees_status', 50)->nullable()->after('application_status');
            $table->string('enrolment_no', 100)->nullable()->after('fees_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student', function (Blueprint $table) {
            $table->dropColumn([
                'school_educator_name',
                'school_educator_no',
                'geo_tagging_school_address',
                'initiator_contact',
                'application_status',
                'fees_status',
                'enrolment_no'
            ]);
        });
    }
};

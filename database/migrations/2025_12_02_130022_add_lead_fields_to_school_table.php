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
        Schema::table('school', function (Blueprint $table) {
            $table->unsignedBigInteger('lead_id')->nullable()->after('school_id');
            $table->unsignedBigInteger('initiator_id')->nullable()->after('lead_id');
            $table->string('referral_link', 255)->nullable()->after('website')->comment('School referral link');

            $table->foreign('lead_id')->references('lead_id')->on('school_leads')->onDelete('set null');
            $table->foreign('initiator_id')->references('sysuser_id')->on('sysuser')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('school', function (Blueprint $table) {
            $table->dropForeign(['lead_id']);
            $table->dropForeign(['initiator_id']);
            $table->dropColumn(['lead_id', 'initiator_id', 'referral_link']);
        });
    }
};

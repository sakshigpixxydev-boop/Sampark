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
        Schema::table('school_leads', function (Blueprint $table) {
            $table->longText('tc_content')->nullable()->after('notes');
            $table->timestamp('tc_agreed_at')->nullable()->after('tc_content');
            $table->date('tc_expiry_date')->nullable()->after('tc_agreed_at');
            $table->timestamp('expiry_notification_sent_at')->nullable()->after('tc_expiry_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('school_leads', function (Blueprint $table) {
            $table->dropColumn(['tc_content', 'tc_agreed_at', 'tc_expiry_date', 'expiry_notification_sent_at']);
        });
    }
};

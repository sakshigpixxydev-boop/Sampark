<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('student', function (Blueprint $table) {
            if (!Schema::hasColumn('student', 'is_lead')) {
                // NOT NULL default 0 is required: the Student global scope filters on
                // `student.is_lead = 0`, so a NULL here would hide an existing student.
                $table->boolean('is_lead')->default(false)->after('school_approval_status')->index();
            }
            if (!Schema::hasColumn('student', 'lead_captured_at')) {
                $table->timestamp('lead_captured_at')->nullable()->after('is_lead');
            }
            if (!Schema::hasColumn('student', 'lead_source')) {
                $table->string('lead_source', 30)->nullable()->after('lead_captured_at');
            }
            if (!Schema::hasColumn('student', 'lead_payment_link_sent_at')) {
                $table->timestamp('lead_payment_link_sent_at')->nullable()->after('lead_source');
            }
            if (!Schema::hasColumn('student', 'lead_converted_at')) {
                $table->timestamp('lead_converted_at')->nullable()->after('lead_payment_link_sent_at');
            }
        });

        // Belt and braces: guarantee no existing row is left with a NULL is_lead.
        DB::table('student')->whereNull('is_lead')->update(['is_lead' => 0]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student', function (Blueprint $table) {
            if (Schema::hasColumn('student', 'is_lead')) {
                $table->dropIndex(['is_lead']);
            }
            $table->dropColumn([
                'is_lead',
                'lead_captured_at',
                'lead_source',
                'lead_payment_link_sent_at',
                'lead_converted_at',
            ]);
        });
    }
};

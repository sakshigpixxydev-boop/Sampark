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
        if (Schema::hasTable('school')) {
            Schema::table('school', function (Blueprint $table) {
                if (!Schema::hasColumn('school', 'agreement_pdf_path')) {
                    $table->string('agreement_pdf_path', 255)->nullable()->after('notes');
                }
                if (!Schema::hasColumn('school', 'agreement_status')) {
                    $table->string('agreement_status', 50)->default('pending')->after('agreement_pdf_path');
                }
            });
        }

        if (Schema::hasTable('sysuser')) {
            Schema::table('sysuser', function (Blueprint $table) {
                if (!Schema::hasColumn('sysuser', 'signature_path')) {
                    $table->string('signature_path', 255)->nullable()->after('inactive');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('school')) {
            Schema::table('school', function (Blueprint $table) {
                $table->dropColumn(['agreement_pdf_path', 'agreement_status']);
            });
        }

        if (Schema::hasTable('sysuser')) {
            Schema::table('sysuser', function (Blueprint $table) {
                $table->dropColumn(['signature_path']);
            });
        }
    }
};

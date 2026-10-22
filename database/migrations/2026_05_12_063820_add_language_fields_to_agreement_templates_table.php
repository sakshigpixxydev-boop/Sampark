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
        Schema::table('agreement_templates', function (Blueprint $table) {
            $table->renameColumn('content', 'content_en');
            $table->longText('content_hi')->nullable()->after('content_en');
            $table->longText('content_gu')->nullable()->after('content_hi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agreement_templates', function (Blueprint $table) {
            $table->renameColumn('content_en', 'content');
            $table->dropColumn(['content_hi', 'content_gu']);
        });
    }
};

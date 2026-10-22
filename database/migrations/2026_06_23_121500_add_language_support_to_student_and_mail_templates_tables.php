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
        // 1. Add selected_language to student table
        Schema::table('student', function (Blueprint $table) {
            $table->string('selected_language', 10)->default('en')->after('materiallanguage');
        });

        // 2. Rename and add translation fields in mail_templates table
        Schema::table('mail_templates', function (Blueprint $table) {
            $table->renameColumn('title', 'title_en');
            $table->renameColumn('content', 'content_en');
        });

        Schema::table('mail_templates', function (Blueprint $table) {
            $table->string('title_hi', 255)->nullable()->after('title_en');
            $table->string('title_gu', 255)->nullable()->after('title_hi');
            $table->longText('content_hi')->nullable()->after('content_en');
            $table->longText('content_gu')->nullable()->after('content_hi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Remove selected_language from student table
        Schema::table('student', function (Blueprint $table) {
            $table->dropColumn('selected_language');
        });

        // 2. Revert translation fields in mail_templates table
        Schema::table('mail_templates', function (Blueprint $table) {
            $table->dropColumn(['title_hi', 'title_gu', 'content_hi', 'content_gu']);
            $table->renameColumn('title_en', 'title');
            $table->renameColumn('content_en', 'content');
        });
    }
};

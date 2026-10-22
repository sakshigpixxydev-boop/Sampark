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
        Schema::table('global_templates', function (Blueprint $table) {
            $table->string('type')->default('Email')->change();
        });

        Schema::table('student_announcements', function (Blueprint $table) {
            $table->string('communication_type')->default('Email')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('global_templates', function (Blueprint $table) {
            $table->enum('type', ['Email', 'WhatsApp', 'Both'])->default('Email')->change();
        });

        Schema::table('student_announcements', function (Blueprint $table) {
            $table->enum('communication_type', ['Email', 'WhatsApp'])->default('Email')->change();
        });
    }
};

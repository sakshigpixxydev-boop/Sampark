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
        if (Schema::hasTable('tickets') && Schema::hasColumn('tickets', 'school_id')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->unsignedBigInteger('school_id')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('tickets') && Schema::hasColumn('tickets', 'school_id')) {
            Schema::table('tickets', function (Blueprint $table) {
                // To revert, it would require school_id to be not nullable.
                // However, if there are existing NULL rows, this would fail.
                $table->unsignedBigInteger('school_id')->nullable(false)->change();
            });
        }
    }
};

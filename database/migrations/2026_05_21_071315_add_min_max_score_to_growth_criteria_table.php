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
        Schema::table('growth_criteria', function (Blueprint $table) {
            $table->decimal('min_score', 8, 2)->default(0)->after('criterion_name');
            $table->decimal('max_score', 8, 2)->default(10)->after('min_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('growth_criteria', function (Blueprint $table) {
            $table->dropColumn(['min_score', 'max_score']);
        });
    }
};

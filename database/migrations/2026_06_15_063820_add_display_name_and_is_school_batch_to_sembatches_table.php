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
        Schema::table('sembatches', function (Blueprint $table) {
            $table->string('display_name', 100)->nullable()->after('code_prefix');
            $table->tinyInteger('is_school_batch')->default(0)->after('display_name')->comment('0 = Individual, 1 = School');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sembatches', function (Blueprint $table) {
            $table->dropColumn(['display_name', 'is_school_batch']);
        });
    }
};

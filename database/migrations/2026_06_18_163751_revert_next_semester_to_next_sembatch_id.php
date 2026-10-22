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
            $table->dropColumn('next_semester');
            $table->unsignedBigInteger('next_sembatch_id')->nullable()->after('semester');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sembatches', function (Blueprint $table) {
            $table->dropColumn('next_sembatch_id');
            $table->integer('next_semester')->nullable();
        });
    }
};

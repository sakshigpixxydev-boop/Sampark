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
            $table->unsignedBigInteger('std_id')->nullable()->after('sembatches_id');
            $table->foreign('std_id')->references('std_id')->on('ms_standard');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sembatches', function (Blueprint $table) {
            $table->dropForeign(['std_id']);
            $table->dropColumn('std_id');
        });
    }
};

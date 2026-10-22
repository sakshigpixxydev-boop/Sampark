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
            $table->dropColumn('fees');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sembatches', function (Blueprint $table) {
            $table->decimal('fees', 10, 2)->nullable();
        });
    }
};

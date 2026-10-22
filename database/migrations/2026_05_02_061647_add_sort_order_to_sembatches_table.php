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
            $table->integer('sort_order')->default(0)->after('fees');
            $table->string('code_prefix', 255)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sembatches', function (Blueprint $table) {
            $table->dropColumn('sort_order');
            $table->string('code_prefix', 255)->nullable(false)->change();
        });
    }
};

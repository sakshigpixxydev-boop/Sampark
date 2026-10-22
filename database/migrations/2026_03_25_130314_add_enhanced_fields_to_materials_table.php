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
        Schema::table('materials', function (Blueprint $table) {
            $table->decimal('discounted_price', 10, 2)->nullable()->after('price');
            $table->date('discount_start_date')->nullable()->after('discounted_price');
            $table->date('discount_end_date')->nullable()->after('discount_start_date');
            $table->json('filters')->nullable()->after('inactive');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            $table->dropColumn(['discounted_price', 'discount_start_date', 'discount_end_date', 'filters']);
        });
    }
};

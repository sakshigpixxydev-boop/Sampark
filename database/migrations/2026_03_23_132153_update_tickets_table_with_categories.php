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
        Schema::table('tickets', function (Blueprint $table) {
            $table->unsignedBigInteger('ticket_category_id')->nullable()->after('school_id');
            $table->foreign('ticket_category_id')->references('id')->on('ticket_categories')->onDelete('set null');
        });

        // Optional: Move data from ticket_type to ticket_categories then update ticket_category_id
        // For now, keep it simple.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['ticket_category_id']);
            $table->dropColumn('ticket_category_id');
        });
    }
};

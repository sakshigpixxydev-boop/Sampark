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
        Schema::table('certificates', function (Blueprint $table) {
            if (!Schema::hasColumn('certificates', 'sembatches_id')) {
                $table->unsignedBigInteger('sembatches_id')->after('reference_id')->nullable();
            }
            if (!Schema::hasColumn('certificates', 'start_date')) {
                $table->date('start_date')->after('recipient_name')->nullable();
            }
            if (!Schema::hasColumn('certificates', 'end_date')) {
                $table->date('end_date')->after('start_date')->nullable();
            }
            
            if (Schema::hasColumn('certificates', 'title')) {
                $table->dropColumn('title');
            }
            if (Schema::hasColumn('certificates', 'description')) {
                $table->dropColumn('description');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            if (!Schema::hasColumn('certificates', 'title')) {
                $table->string('title')->nullable();
            }
            if (!Schema::hasColumn('certificates', 'description')) {
                $table->text('description')->nullable();
            }
            
            if (Schema::hasColumn('certificates', 'sembatches_id')) {
                $table->dropColumn('sembatches_id');
            }
            if (Schema::hasColumn('certificates', 'start_date')) {
                $table->dropColumn('start_date');
            }
            if (Schema::hasColumn('certificates', 'end_date')) {
                $table->dropColumn('end_date');
            }
        });
    }
};

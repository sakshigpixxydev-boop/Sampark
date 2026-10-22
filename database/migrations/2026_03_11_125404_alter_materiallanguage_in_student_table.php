<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Change column to nullable bigInteger first (without FK) so we can update values beyond enum range
        Schema::table('student', function (Blueprint $table) {
            $table->unsignedBigInteger('materiallanguage')->nullable()->change();
        });

        // 2. Map old enum values to their correct IDs in the language table
        // Old mapping: 1 - English, 2 - Hindi
        $englishId = DB::table('language')->where('languagename', 'English')->value('lg_id');
        $hindiId = DB::table('language')->where('languagename', 'Hindi')->value('lg_id');

        if ($englishId) {
            DB::table('student')->where('materiallanguage', '1')->update(['materiallanguage' => $englishId]);
        }
        if ($hindiId) {
            DB::table('student')->where('materiallanguage', '2')->update(['materiallanguage' => $hindiId]);
        }

        // 3. Clear any remaining invalid values that don't exist in the language table
        DB::table('student')
            ->whereNotNull('materiallanguage')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                      ->from('language')
                      ->whereRaw('language.lg_id = student.materiallanguage');
            })
            ->update(['materiallanguage' => null]);

        // 4. Finally add the foreign key constraint
        Schema::table('student', function (Blueprint $table) {
            $table->foreign('materiallanguage')->references('lg_id')->on('language')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student', function (Blueprint $table) {
            // Safely drop foreign key by name if it exists (doctrine/dbal doesn't always support this elegantly on all DBs, so we use a standard approach)
            $table->dropForeign(['materiallanguage']);
            $table->enum('materiallanguage', ['1', '2'])->nullable()->change();
        });
    }
};

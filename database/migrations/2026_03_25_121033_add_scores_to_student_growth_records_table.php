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
        if (!Schema::hasColumn('student_growth_records', 'scores')) {
            Schema::table('student_growth_records', function (Blueprint $table) {
                $table->json('scores')->nullable()->after('behaviour');
            });
        }

        // Migrate existing data to JSON
        $records = DB::table('student_growth_records')->get();
        $criteria = DB::table('growth_criteria')->get()->keyBy('criterion_name');

        foreach ($records as $record) {
            $scores = [];
            $mapping = [
                'discipline' => 'Discipline',
                'leadership' => 'Leadership',
                'communication' => 'Communication',
                'teamwork' => 'Teamwork',
                'participation' => 'Participation',
                'behaviour' => 'Behaviour'
            ];

            foreach ($mapping as $col => $name) {
                if (isset($criteria[$name])) {
                    $scores[$criteria[$name]->criterion_id] = $record->$col;
                }
            }

            DB::table('student_growth_records')
                ->where('growth_id', $record->growth_id)
                ->update(['scores' => json_encode($scores)]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_growth_records', function (Blueprint $table) {
            $table->dropColumn('scores');
        });
    }
};


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
        $tables = [
            'ms_country',
            'ms_state',
            'ms_dist',
            'ms_taluko',
            'ms_standard',
            'ms_relation',
            'ms_kshetra',
            'ms_shikharmandir',
            'ms_satsangmandal',
            'ms_eductionalboard',
            'studenttype',
            'schooltype',
            'paymentmode',
            'usertype',
            'permission',
            'role',
            'sysuser',
            'school',
            'sembatches',
            'guardian',
            'parcelcompany',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    if (Schema::hasColumn($table->getTable(), 'create_at')) {
                        $table->renameColumn('create_at', 'created_at');
                    }
                    if (Schema::hasColumn($table->getTable(), 'update_at')) {
                        $table->renameColumn('update_at', 'updated_at');
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'ms_country',
            'ms_state',
            'ms_dist',
            'ms_taluko',
            'ms_standard',
            'ms_relation',
            'ms_kshetra',
            'ms_shikharmandir',
            'ms_satsangmandal',
            'ms_eductionalboard',
            'studenttype',
            'schooltype',
            'paymentmode',
            'usertype',
            'permission',
            'role',
            'sysuser',
            'school',
            'sembatches',
            'guardian',
            'parcelcompany',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    if (Schema::hasColumn($table->getTable(), 'created_at')) {
                        $table->renameColumn('created_at', 'create_at');
                    }
                    if (Schema::hasColumn($table->getTable(), 'updated_at')) {
                        $table->renameColumn('updated_at', 'update_at');
                    }
                });
            }
        }
    }
};

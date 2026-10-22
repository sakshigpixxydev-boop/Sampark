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
        Schema::table('school', function (Blueprint $table) {
            if (!Schema::hasColumn('school', 'geolocation')) {
                $table->string('geolocation', 500)->nullable()->after('website');
            }
            if (!Schema::hasColumn('school', 'auth2_role')) {
                $table->string('auth2_role', 50)->nullable()->after('auth_phone');
                $table->string('auth2_role_other', 100)->nullable()->after('auth2_role');
                $table->string('auth2_name', 200)->nullable()->after('auth2_role_other');
                $table->string('auth2_email', 150)->nullable()->after('auth2_name');
                $table->string('auth2_phone', 15)->nullable()->after('auth2_email');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('school', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('school', 'geolocation')) {
                $cols[] = 'geolocation';
            }
            if (Schema::hasColumn('school', 'auth2_role')) {
                $cols = array_merge($cols, ['auth2_role', 'auth2_role_other', 'auth2_name', 'auth2_email', 'auth2_phone']);
            }
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};

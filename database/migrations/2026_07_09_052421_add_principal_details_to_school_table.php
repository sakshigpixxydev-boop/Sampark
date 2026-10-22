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
            $table->string('auth_role', 50)->nullable()->after('admincontact');
            $table->string('auth_role_other', 100)->nullable()->after('auth_role');
            $table->string('auth_name', 200)->nullable()->after('auth_role_other');
            $table->string('auth_email', 150)->nullable()->after('auth_name');
            $table->string('auth_phone', 15)->nullable()->after('auth_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('school', function (Blueprint $table) {
            $table->dropColumn(['auth_role', 'auth_role_other', 'auth_name', 'auth_email', 'auth_phone']);
        });
    }
};

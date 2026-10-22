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
        Schema::table('student', function (Blueprint $table) {
            $table->string('satsang_karyakar_name', 255)->nullable()->after('satsangmandal_id');
            $table->string('satsang_karyakar_contact', 20)->nullable()->after('satsang_karyakar_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student', function (Blueprint $table) {
            $table->dropColumn(['satsang_karyakar_name', 'satsang_karyakar_contact']);
        });
    }
};

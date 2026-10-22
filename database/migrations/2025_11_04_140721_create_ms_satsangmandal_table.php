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
        Schema::create('ms_satsangmandal', function (Blueprint $table) {
            $table->id('satsangmandal_id');
            $table->string('mandalname', 100)->nullable();
            $table->string('karyakarname', 100)->nullable();
            $table->string('karyakarcontact', 20)->nullable();
            $table->date('created_at')->useCurrent();
            $table->date('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ms_satsangmandal');
    }
};

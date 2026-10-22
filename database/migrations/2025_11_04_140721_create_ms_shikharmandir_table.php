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
        Schema::create('ms_shikharmandir', function (Blueprint $table) {
            $table->id('shikharm_id');
            $table->string('mname', 100);
            $table->string('maddress', 500)->nullable();
            $table->date('created_at')->useCurrent();
            $table->date('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ms_shikharmandir');
    }
};

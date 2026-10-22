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
        Schema::create('ms_eductionalboard', function (Blueprint $table) {
            $table->id('eduboard_id');
            $table->string('boardname', 100)->nullable()->comment('CBSE, ICSE, State Board, etc');
            $table->date('created_at')->useCurrent();
            $table->date('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ms_eductionalboard');
    }
};

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
        Schema::create('ms_state', function (Blueprint $table) {
            $table->id('state_id');
            $table->unsignedBigInteger('country_id');
            $table->string('sname', 100);
            $table->date('created_at')->useCurrent();
            $table->date('updated_at')->nullable();
            
            $table->foreign('country_id')->references('country_id')->on('ms_country')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ms_state');
    }
};

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
        Schema::create('ms_taluko', function (Blueprint $table) {
            $table->id('takula_id');
            $table->unsignedBigInteger('dist_id');
            $table->string('tname', 100);
            $table->date('created_at')->useCurrent();
            $table->date('updated_at')->nullable();
            
            $table->foreign('dist_id')->references('dist_id')->on('ms_dist')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ms_taluko');
    }
};

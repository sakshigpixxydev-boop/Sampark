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
        Schema::create('ms_dist', function (Blueprint $table) {
            $table->id('dist_id');
            $table->unsignedBigInteger('state_id');
            $table->string('dname', 100);
            $table->date('created_at')->useCurrent();
            $table->date('updated_at')->nullable();
            
            $table->foreign('state_id')->references('state_id')->on('ms_state')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ms_dist');
    }
};

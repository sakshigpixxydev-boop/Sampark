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
        Schema::create('sembatches', function (Blueprint $table) {
            $table->id('sembatches_id');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('capacity')->nullable();
            $table->unsignedBigInteger('next_sembatch_id')->nullable();
            $table->decimal('fees', 10, 2);
            $table->string('code_prefix', 255);
            $table->tinyInteger('status')->default(0);
            $table->date('created_at')->useCurrent();
            $table->date('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sembatches');
    }
};

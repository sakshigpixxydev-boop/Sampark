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
        Schema::create('guardian', function (Blueprint $table) {
            $table->id('guardian_id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('relation_id');
            $table->string('fname', 100);
            $table->string('lname', 100);
            $table->string('mname', 100);
            $table->string('gender', 10)->nullable()->comment('M - Male, F - Female, O - Other');
            $table->string('phone', 10)->nullable();
            $table->string('email', 255)->nullable();
            $table->text('education')->nullable();
            $table->string('work_experience', 255)->nullable();
            $table->text('how_to_help')->nullable();
            $table->date('created_at')->useCurrent();
            $table->date('updated_at')->nullable();
            $table->string('createdby', 100)->nullable();
            
            $table->foreign('student_id')->references('student_id')->on('student')->onDelete('cascade');
            $table->foreign('relation_id')->references('relation_id')->on('ms_relation')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guardian');
    }
};

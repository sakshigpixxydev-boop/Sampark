<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateSembatchesAndFeePlanTables extends Migration
{
    public function up()
    {
        // Update sembatches table
        Schema::table('sembatches', function (Blueprint $table) {
            $table->integer('semester')->nullable()->after('display_name');
            $table->string('edmingle_course_id')->nullable()->after('fees');
            $table->string('edmingle_batch_id')->nullable()->after('edmingle_course_id');
            $table->string('edmingle_course_name')->nullable()->after('edmingle_batch_id');
            $table->string('edmingle_batch_name')->nullable()->after('edmingle_course_name');
            $table->integer('next_semester')->nullable()->after('next_sembatch_id');
        });

        // Drop old columns from sembatches in a separate closure to avoid issues with SQLite if needed, though MySQL can do it together
        Schema::table('sembatches', function (Blueprint $table) {
            $table->dropForeign(['std_id']);
            $table->dropColumn(['std_id', 'is_school_batch', 'sort_order', 'next_sembatch_id']);
        });

        // Update fee_plan table
        Schema::table('fee_plan', function (Blueprint $table) {
            $table->dropUnique('unq_fee_scope');
            $table->dropForeign(['school_id']);
            $table->dropForeign(['std_id']);
            
            $table->date('plan_auto_close_date')->nullable()->after('valid_to');
            $table->dropColumn(['school_id', 'std_id', 'edmingle_course_id', 'edmingle_batch_id']);
        });
    }

    public function down()
    {
        Schema::table('sembatches', function (Blueprint $table) {
            $table->integer('std_id')->nullable();
            $table->integer('is_school_batch')->nullable();
            $table->integer('sort_order')->nullable();
            $table->integer('next_sembatch_id')->nullable();
            
            $table->dropColumn([
                'semester', 
                'edmingle_course_id', 
                'edmingle_batch_id', 
                'edmingle_course_name', 
                'edmingle_batch_name', 
                'next_semester'
            ]);
        });

        Schema::table('fee_plan', function (Blueprint $table) {
            $table->integer('school_id')->nullable();
            $table->integer('std_id')->nullable();
            $table->string('edmingle_course_id')->nullable();
            $table->string('edmingle_batch_id')->nullable();
            
            $table->dropColumn('plan_auto_close_date');
        });
    }
}

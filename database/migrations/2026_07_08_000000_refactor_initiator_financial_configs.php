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
        Schema::table('initiator_financial_configs', function (Blueprint $table) {
            $table->dropForeign(['sysuser_id']);
            $table->dropColumn('sysuser_id');
            $table->string('name')->after('id')->nullable();
        });

        Schema::create('initiator_financial_config_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sysuser_id');
            $table->unsignedBigInteger('config_id');
            $table->timestamps();

            $table->foreign('sysuser_id')->references('sysuser_id')->on('sysuser')->onDelete('cascade');
            $table->foreign('config_id')->references('id')->on('initiator_financial_configs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('initiator_financial_config_user');

        Schema::table('initiator_financial_configs', function (Blueprint $table) {
            $table->unsignedBigInteger('sysuser_id')->nullable();
            $table->dropColumn('name');
            $table->foreign('sysuser_id')->references('sysuser_id')->on('sysuser')->onDelete('cascade');
        });
    }
};

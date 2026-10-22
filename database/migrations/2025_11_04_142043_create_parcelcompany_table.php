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
        Schema::create('parcelcompany', function (Blueprint $table) {
            $table->id('parcelcom_id');
            $table->string('companyname', 255)->nullable();
            $table->string('trackingurl', 500)->nullable()->comment('URL format for tracking shipments (e.g., https://track.bludart.com/?id={tracking_number}).');
            $table->string('apiendpoint', 255)->nullable()->comment('Base URL if integrated via API');
            $table->string('apikey', 255)->nullable();
            $table->string('supportcontact', 50)->nullable();
            $table->string('deliverytimeestimate', 50)->nullable()->comment('Typical delivery time (e.g., "2-5 days").');
            $table->boolean('inactive')->default(false);
            $table->date('created_at')->useCurrent();
            $table->date('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parcelcompany');
    }
};

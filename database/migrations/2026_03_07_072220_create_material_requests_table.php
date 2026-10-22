<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_requests', function (Blueprint $row) {
            $row->id();
            $row->unsignedBigInteger('school_id');
            $row->unsignedBigInteger('material_id');
            $row->integer('quantity');
            $row->decimal('total_amount', 10, 2);
            $row->string('delivery_type')->comment('digital, printable');
            $row->string('status')->default('pending')->comment('pending, approved, dispatched, completed');
            $row->text('admin_remarks')->nullable();
            $row->timestamps();

            // Foreign keys
            $row->foreign('school_id')->references('school_id')->on('school')->onDelete('cascade');
            $row->foreign('material_id')->references('id')->on('materials')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_requests');
    }
};

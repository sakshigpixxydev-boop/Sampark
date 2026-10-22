<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('materials', function (Blueprint $row) {
            $row->id();
            $row->string('name', 255);
            $row->text('description')->nullable();
            $row->decimal('price', 10, 2);
            $row->tinyInteger('material_type')->comment('0: Digital, 1: Printable');
            $row->boolean('inactive')->default(false);
            $row->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('materials');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('upload_batch', function (Blueprint $table) {
            if (!Schema::hasColumn('upload_batch', 'payment_document_path')) {
                $table->string('payment_document_path', 500)->nullable()->after('bank_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('upload_batch', function (Blueprint $table) {
            if (Schema::hasColumn('upload_batch', 'payment_document_path')) {
                $table->dropColumn('payment_document_path');
            }
        });
    }
};

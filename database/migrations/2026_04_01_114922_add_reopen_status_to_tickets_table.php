<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Alter enum to include 'Reopen'
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE tickets MODIFY COLUMN status ENUM('Open', 'In Progress', 'Resolved', 'Closed', 'Reopen') DEFAULT 'Open'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert enum, moving any 'Reopen' to 'Open' first to prevent error (optional)
        if (DB::getDriverName() !== 'sqlite') {
            DB::update("UPDATE tickets SET status = 'Open' WHERE status = 'Reopen'");
            DB::statement("ALTER TABLE tickets MODIFY COLUMN status ENUM('Open', 'In Progress', 'Resolved', 'Closed') DEFAULT 'Open'");
        }
    }
};

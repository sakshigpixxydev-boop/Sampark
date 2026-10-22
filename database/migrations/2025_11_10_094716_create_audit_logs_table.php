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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('event_type', 50); // CREATE, UPDATE, DELETE, LOGIN, LOGOUT, etc.
            $table->string('auditable_type', 255); // Model class name
            $table->unsignedBigInteger('auditable_id')->nullable(); // Model ID
            $table->unsignedBigInteger('user_id')->nullable(); // User who performed the action
            $table->string('user_type', 255)->nullable(); // User model class
            $table->text('description')->nullable(); // Human-readable description
            $table->json('old_values')->nullable(); // Previous values (for updates)
            $table->json('new_values')->nullable(); // New values (for creates/updates)
            $table->string('ip_address', 45)->nullable(); // IPv4 or IPv6
            $table->string('user_agent', 500)->nullable(); // Browser/client info
            $table->string('url', 500)->nullable(); // Request URL
            $table->string('method', 10)->nullable(); // HTTP method (GET, POST, etc.)
            $table->json('metadata')->nullable(); // Additional context data
            $table->timestamps();
            
            // Indexes for better query performance
            $table->index(['auditable_type', 'auditable_id']);
            $table->index(['user_id', 'user_type']);
            $table->index('event_type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->string('level', 20)->default('info'); // debug, info, notice, warning, error, critical
            $table->string('action', 100);                // login_success, login_failed, admin_login_failed, etc.
            $table->string('subject_type', 100)->nullable(); // model class
            $table->unsignedBigInteger('subject_id')->nullable(); // model id
            $table->string('user_type', 20)->nullable();  // 'admin' | 'user' | null (guest)
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_label', 100)->nullable(); // email ou pseudo au moment du log
            $table->json('properties')->nullable();         // contexte libre
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['level', 'created_at']);
            $table->index(['action', 'created_at']);
            $table->index(['user_type', 'user_id']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};

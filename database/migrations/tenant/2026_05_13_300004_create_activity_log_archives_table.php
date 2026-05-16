<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_log_archives', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('user_id', 36)->nullable();
            $table->string('event');
            $table->string('description');
            $table->string('subject_type')->nullable();
            $table->string('subject_id', 36)->nullable();
            $table->json('properties')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('created_at');
            $table->timestamp('archived_at')->useCurrent();

            $table->index(['user_id', 'created_at']);
            $table->index('created_at');
            $table->index('archived_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_log_archives');
    }
};

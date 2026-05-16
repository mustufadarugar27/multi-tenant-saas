<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('actor_id')->nullable();        // null = system action
            $table->string('actor_type')->default('user');
            $table->string('action');                   // e.g. user.login, permission.granted
            $table->string('resource_type')->nullable(); // e.g. App\Models\User
            $table->string('resource_id', 36)->nullable();
            $table->json('old_values')->nullable();      // snapshot before change
            $table->json('new_values')->nullable();      // snapshot after change
            $table->json('metadata')->nullable();        // extra context (request_id, etc.)
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('status')->default('success'); // success | failure
            $table->timestamp('created_at')->useCurrent();

            // Efficient query patterns for compliance reporting
            $table->index(['actor_id', 'created_at']);
            $table->index(['action', 'created_at']);
            $table->index(['resource_type', 'resource_id']);
            $table->index(['status', 'created_at']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};

<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('project_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->uuid('assigned_to')->nullable();
            $table->uuid('created_by');
            $table->string('priority')->default('medium');
            $table->string('status')->default('todo');
            $table->date('due_date')->nullable();
            $table->decimal('estimated_hours', 6, 2)->nullable();
            $table->decimal('actual_hours', 6, 2)->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('project_id');
            $table->index('assigned_to');
            $table->index('created_by');
            $table->index('status');
            $table->index('priority');
            $table->index('due_date');
            $table->index('created_at');
            $table->index(['project_id', 'status']);
            $table->index(['project_id', 'assigned_to']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};

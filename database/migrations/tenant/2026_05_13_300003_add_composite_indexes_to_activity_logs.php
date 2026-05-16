<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table): void {
            // Composite indexes for common filter+sort patterns
            $table->index(['subject_type', 'subject_id', 'created_at'], 'al_subject_created');
            $table->index(['user_id', 'event', 'created_at'], 'al_user_event_created');
            $table->index(['event', 'created_at'], 'al_event_created');
        });
    }

    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table): void {
            $table->dropIndex('al_subject_created');
            $table->dropIndex('al_user_event_created');
            $table->dropIndex('al_event_created');
        });
    }
};

<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table): void {
            // morphs() created notifiable_id as UNSIGNED BIGINT — too small for UUIDs.
            // Change to CHAR(36) to match UUID primary keys.
            $table->dropIndex(['notifiable_type', 'notifiable_id', 'read_at']);

            $table->string('notifiable_id', 36)->change();

            $table->index(['notifiable_type', 'notifiable_id', 'read_at']);
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table): void {
            $table->dropIndex(['notifiable_type', 'notifiable_id', 'read_at']);

            $table->unsignedBigInteger('notifiable_id')->change();

            $table->index(['notifiable_type', 'notifiable_id', 'read_at']);
        });
    }
};

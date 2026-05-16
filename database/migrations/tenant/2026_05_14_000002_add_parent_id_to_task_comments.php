<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('task_comments', function (Blueprint $table): void {
            $table->uuid('parent_id')->nullable()->after('user_id');
            $table->unsignedSmallInteger('depth')->default(0)->after('parent_id'); // max depth enforced in service
            $table->index('parent_id');
        });
    }

    public function down(): void
    {
        Schema::table('task_comments', function (Blueprint $table): void {
            $table->dropIndex(['parent_id']);
            $table->dropColumn(['parent_id', 'depth']);
        });
    }
};

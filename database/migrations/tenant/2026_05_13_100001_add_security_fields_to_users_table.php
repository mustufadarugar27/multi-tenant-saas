<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->unsignedTinyInteger('failed_login_count')->default(0)->after('last_login_at');
            $table->timestamp('locked_until')->nullable()->after('failed_login_count');
            $table->timestamp('password_changed_at')->nullable()->after('locked_until');
            $table->index('locked_until');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex(['locked_until']);
            $table->dropColumn(['failed_login_count', 'locked_until', 'password_changed_at']);
        });
    }
};

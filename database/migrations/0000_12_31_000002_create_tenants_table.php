<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTenantsTable extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table): void {
            // stancl uses a string (UUID) PK — NOT auto-increment
            $table->string('id')->primary();

            $table->string('name');
            $table->string('slug')->unique();
            $table->string('status')->default('trial');   // trial|active|suspended|cancelled
            $table->json('settings')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('suspended_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // stancl stores arbitrary tenant attributes in this JSON column
            $table->json('data')->nullable();

            $table->index('slug');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
}

<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pending_registrations', function (Blueprint $table): void {
            $table->id();
            $table->string('token')->unique();
            $table->foreignId('plan_id')->nullable()->constrained()->nullOnDelete();
            $table->string('billing_cycle', 10)->nullable();
            $table->string('company_name');
            $table->string('slug');
            $table->string('name');
            $table->string('email');
            $table->string('password');
            $table->string('stripe_session_id')->nullable();
            $table->string('status', 20)->default('pending');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pending_registrations');
    }
};

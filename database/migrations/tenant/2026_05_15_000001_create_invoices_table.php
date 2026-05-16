<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('stripe_invoice_id')->nullable()->unique();
            $table->unsignedBigInteger('amount');
            $table->string('currency', 10)->default('usd');
            $table->string('status', 20)->default('open');
            $table->timestamp('paid_at')->nullable();
            $table->string('invoice_pdf')->nullable();
            $table->string('hosted_invoice_url')->nullable();
            $table->timestamp('period_start')->nullable();
            $table->timestamp('period_end')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};

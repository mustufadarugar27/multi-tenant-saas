<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table): void {
            $table->string('stripe_customer_id')->nullable()->after('suspended_at');
            $table->foreignId('plan_id')->nullable()->constrained()->nullOnDelete()->after('stripe_customer_id');
            $table->string('billing_cycle', 10)->nullable()->after('plan_id');
            $table->string('subscription_status', 20)->nullable()->after('billing_cycle');
            $table->string('stripe_subscription_id')->nullable()->after('subscription_status');
            $table->timestamp('subscription_ends_at')->nullable()->after('stripe_subscription_id');
            $table->timestamp('grace_period_ends_at')->nullable()->after('subscription_ends_at');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table): void {
            $table->dropColumn([
                'stripe_customer_id',
                'plan_id',
                'billing_cycle',
                'subscription_status',
                'stripe_subscription_id',
                'subscription_ends_at',
                'grace_period_ends_at',
            ]);
        });
    }
};

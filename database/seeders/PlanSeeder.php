<?php


namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name'          => 'Starter',
                'slug'          => 'starter',
                'description'   => 'Perfect for small teams just getting started.',
                'price_monthly' => 0.00,
                'price_yearly'  => 0.00,
                'features'      => [
                    'Up to 5 users',
                    '10 projects',
                    '5GB storage',
                    'Basic task management',
                    'Email support',
                ],
                'max_users'              => 5,
                'storage_gb'             => 5,
                'stripe_monthly_price_id' => null,
                'stripe_yearly_price_id'  => null,
                'is_active'              => true,
                'is_free'                => true,
                'sort_order'             => 1,
            ],
            [
                'name'          => 'Professional',
                'slug'          => 'professional',
                'description'   => 'For growing teams that need more power.',
                'price_monthly' => 29.00,
                'price_yearly'  => 278.40,
                'features'      => [
                    'Up to 25 users',
                    '50GB storage',
                    'Advanced task management',
                ],
                'max_users'              => 25,
                'storage_gb'             => 50,
                'stripe_monthly_price_id' => env('STRIPE_BUSINESS_MONTHLY_PRICE_ID'),
                'stripe_yearly_price_id'  => env('STRIPE_BUSINESS_YEARLY_PRICE_ID'),
                'is_active'              => true,
                'is_free'                => false,
                'sort_order'             => 2,
            ],
            [
                'name'          => 'Enterprise',
                'slug'          => 'enterprise',
                'description'   => 'For large organizations with advanced needs.',
                'price_monthly' => 99.00,
                'price_yearly'  => 950.40,
                'features'      => [
                    'Unlimited users',
                    '500GB storage',
                    'Everything in Professional',
                    'SSO / SAML',
                    'Custom roles & permissions',
                    'Dedicated account manager'
                ],
                'max_users'              => 500,
                'storage_gb'             => 500,
                'stripe_monthly_price_id' => env('STRIPE_ENTERPRISE_MONTHLY_PRICE_ID'),
                'stripe_yearly_price_id'  => env('STRIPE_ENTERPRISE_YEARLY_PRICE_ID'),
                'is_active'              => true,
                'is_free'                => false,
                'sort_order'             => 3,
            ],
        ];

        foreach ($plans as $data) {
            Plan::updateOrCreate(['slug' => $data['slug']], $data);
        }

        $this->command->info('Plans seeded: Starter, Professional, Enterprise');
    }
}

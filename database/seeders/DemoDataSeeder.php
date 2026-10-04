<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Merchant;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $merchant = Merchant::create([
            'name' => 'Demo SaaS Merchant',
            'slug' => 'demo-saas',
        ]);

        $basicPlan = Plan::create([
            'merchant_id' => $merchant->id,
            'name' => 'Basic',
            'base_price' => 29.00,
            'billing_cycle' => 'monthly',
            'included_units' => 1000,
            'overage_rate' => 0.05,
            'is_active' => true,
        ]);

        $proPlan = Plan::create([
            'merchant_id' => $merchant->id,
            'name' => 'Pro',
            'base_price' => 99.00,
            'billing_cycle' => 'monthly',
            'included_units' => 5000,
            'overage_rate' => 0.03,
            'is_active' => true,
        ]);

        $enterprisePlan = Plan::create([
            'merchant_id' => $merchant->id,
            'name' => 'Enterprise',
            'base_price' => 299.00,
            'billing_cycle' => 'monthly',
            'included_units' => 25000,
            'overage_rate' => 0.01,
            'is_active' => true,
        ]);

        foreach (range(1, 10) as $number) {
            $customer = Customer::create([
                'merchant_id' => $merchant->id,
                'name' => "Customer {$number}",
                'email' => "customer{$number}@example.com",
            ]);

            $plan = match ($number % 3) {
                0 => $enterprisePlan,
                1 => $basicPlan,
                default => $proPlan,
            };

            Subscription::create([
                'customer_id' => $customer->id,
                'plan_id' => $plan->id,
                'starts_at' => now()->startOfMonth(),
                'ends_at' => null,
                'status' => 'active',
            ]);
        }
    }
}
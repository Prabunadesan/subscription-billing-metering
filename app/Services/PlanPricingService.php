<?php

namespace App\Services;

use App\Models\Plan;
use Illuminate\Support\Facades\Cache;

class PlanPricingService
{
    private const CACHE_TTL = 3600;

    public function get(int $planId): array
    {
        return Cache::remember(
            $this->cacheKey($planId),
            self::CACHE_TTL,
            function () use ($planId) {
                $plan = Plan::query()
                    ->where('is_active', true)
                    ->findOrFail($planId);

                return [
                    'id' => $plan->id,
                    'merchant_id' => $plan->merchant_id,
                    'name' => $plan->name,
                    'base_price' => (float) $plan->base_price,
                    'billing_cycle' => $plan->billing_cycle,
                    'included_units' => (int) $plan->included_units,
                    'overage_rate' => (float) $plan->overage_rate,
                ];
            }
        );
    }

    public function forget(int $planId): void
    {
        Cache::forget($this->cacheKey($planId));
    }

    private function cacheKey(int $planId): string
    {
        return "plan:pricing:{$planId}";
    }
}
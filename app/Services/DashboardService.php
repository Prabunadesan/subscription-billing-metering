<?php

namespace App\Services;

use App\Models\Merchant;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function getDashboard(Merchant $merchant): array
    {
        $today = Carbon::today();

        $currentMonthStart = $today->copy()->startOfMonth();
        $currentMonthEnd = $today->copy()->endOfMonth();

        $previousMonthStart = $today->copy()->subMonth()->startOfMonth();
        $previousMonthEnd = $today->copy()->subMonth()->endOfMonth();

        return [
            'current_cycle' => $this->currentCycleUsage(
                $merchant,
                $currentMonthStart,
                $currentMonthEnd,
                $today
            ),

            'projected_overage_revenue' => $this->projectedOverageRevenue(
                $merchant,
                $currentMonthStart,
                $currentMonthEnd,
                $today
            ),

            'active_plan' => $this->activePlan($merchant),

            'top_customers' => $this->topCustomers(
                $merchant,
                $currentMonthStart,
                $currentMonthEnd
            ),

            'churn_risk' => $this->usageDrops(
                $merchant,
                $currentMonthStart,
                $currentMonthEnd,
                $previousMonthStart,
                $previousMonthEnd
            ),

            'daily_usage' => $this->dailyUsageTrend(
                $merchant,
                $today->copy()->subDays(29),
                $today
            ),

            'system_status' => [
                'cache' => 'Database cache, TTL 60m',
                'aggregation' => 'Queued, chunked (5k rows/batch)',
                'rate_limit' => '100 req/min per IP',
            ],
        ];
    }

    /**
     * Current cycle usage.
     */
    private function currentCycleUsage(
        Merchant $merchant,
        Carbon $start,
        Carbon $end,
        Carbon $today
    ): array {
        $usage = DB::table('daily_usage')
            ->where('merchant_id', $merchant->id)
            ->whereBetween('usage_date', [
                $start->toDateString(),
                $today->toDateString(),
            ])
            ->sum('total_units');

        /*
         * Calculate included units based on subscription
         * segments during the current month.
         */
        $includedUnits = 0;

        $subscriptions = Subscription::query()
            ->whereHas('customer', function ($query) use ($merchant) {
                $query->where('merchant_id', $merchant->id);
            })
            ->with('plan')
            ->where('starts_at', '<=', $end)
            ->where(function ($query) use ($start) {
                $query
                    ->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', $start);
            })
            ->get();

        foreach ($subscriptions as $subscription) {
            if (!$subscription->plan) {
                continue;
            }

            $segmentStart = Carbon::parse($subscription->starts_at)
                ->max($start);

            $segmentEnd = $subscription->ends_at
                ? Carbon::parse($subscription->ends_at)->min($end)
                : $end->copy();

            if ($segmentStart->gt($segmentEnd)) {
                continue;
            }

            $days = $segmentStart->diffInDays($segmentEnd) + 1;

            $cycleDays = $subscription->plan->billing_cycle === 'yearly'
                ? ($segmentStart->isLeapYear() ? 366 : 365)
                : $segmentStart->daysInMonth;

            $includedUnits +=
                $subscription->plan->included_units
                * ($days / $cycleDays);
        }

        $percentage = $includedUnits > 0
            ? ($usage / $includedUnits) * 100
            : 0;

        return [
            'usage' => (int) $usage,
            'included_units' => (int) round($includedUnits),
            'usage_percentage' => round($percentage, 2),
            'start' => $start->toDateString(),
            'end' => $end->toDateString(),
        ];
    }

    /**
     * Project overage revenue for the current billing cycle.
     */
    private function projectedOverageRevenue(
        Merchant $merchant,
        Carbon $cycleStart,
        Carbon $cycleEnd,
        Carbon $today
    ): float {
        $subscriptions = Subscription::query()
            ->whereHas('customer', function ($query) use ($merchant) {
                $query->where('merchant_id', $merchant->id);
            })
            ->with('plan')
            ->where('starts_at', '<=', $cycleEnd)
            ->where(function ($query) use ($cycleStart) {
                $query
                    ->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', $cycleStart);
            })
            ->get();

        $projectedRevenue = 0;

        foreach ($subscriptions as $subscription) {
            if (!$subscription->plan) {
                continue;
            }

            $segmentStart = Carbon::parse($subscription->starts_at)
                ->max($cycleStart);

            $segmentEnd = $subscription->ends_at
                ? Carbon::parse($subscription->ends_at)->min($cycleEnd)
                : $cycleEnd->copy();

            if ($segmentStart->gt($segmentEnd)) {
                continue;
            }

            /*
             * If this segment is still running, project usage
             * until the end of the segment.
             */
            $elapsedEnd = $today->copy()->min($segmentEnd);

            if ($elapsedEnd->lt($segmentStart)) {
                continue;
            }

            $elapsedDays = $segmentStart->diffInDays($elapsedEnd) + 1;
            $segmentDays = $segmentStart->diffInDays($segmentEnd) + 1;

            $usage = DB::table('daily_usage')
                ->where('customer_id', $subscription->customer_id)
                ->where('subscription_id', $subscription->id)
                ->whereBetween('usage_date', [
                    $segmentStart->toDateString(),
                    $elapsedEnd->toDateString(),
                ])
                ->sum('total_units');

            /*
             * If there is already usage, project the remaining
             * usage based on the current daily average.
             */
            if ($elapsedDays > 0 && $elapsedDays < $segmentDays) {
                $projectedUsage =
                    ($usage / $elapsedDays) * $segmentDays;
            } else {
                $projectedUsage = $usage;
            }

            $cycleDays = $subscription->plan->billing_cycle === 'yearly'
                ? ($segmentStart->isLeapYear() ? 366 : 365)
                : $segmentStart->daysInMonth;

            $includedUnits =
                $subscription->plan->included_units
                * ($segmentDays / $cycleDays);

            $overageUnits = max(
                0,
                $projectedUsage - $includedUnits
            );

            $projectedRevenue +=
                $overageUnits * $subscription->plan->overage_rate;
        }

        return round($projectedRevenue, 2);
    }

    /**
     * Most-used active plan for the merchant.
     */
    private function activePlan(Merchant $merchant): ?array
    {
        $plan = DB::table('subscriptions')
            ->join('customers', 'customers.id', '=', 'subscriptions.customer_id')
            ->join('plans', 'plans.id', '=', 'subscriptions.plan_id')
            ->where('customers.merchant_id', $merchant->id)
            ->where('subscriptions.status', 'active')
            ->select(
                'plans.id',
                'plans.name',
                'plans.billing_cycle',
                DB::raw('COUNT(subscriptions.id) as subscription_count')
            )
            ->groupBy(
                'plans.id',
                'plans.name',
                'plans.billing_cycle'
            )
            ->orderByDesc('subscription_count')
            ->first();

        if (!$plan) {
            return null;
        }

        return [
            'id' => $plan->id,
            'name' => $plan->name,
            'billing_cycle' => $plan->billing_cycle,
            'subscription_count' => (int) $plan->subscription_count,
        ];
    }

    /**
     * Top 5 customers by current month usage.
     */
    private function topCustomers(
        Merchant $merchant,
        Carbon $start,
        Carbon $end
    ): array {
        return DB::table('daily_usage')
            ->join(
                'customers',
                'customers.id',
                '=',
                'daily_usage.customer_id'
            )
            ->join(
                'subscriptions',
                'subscriptions.id',
                '=',
                'daily_usage.subscription_id'
            )
            ->join(
                'plans',
                'plans.id',
                '=',
                'subscriptions.plan_id'
            )
            ->where('daily_usage.merchant_id', $merchant->id)
            ->whereBetween('daily_usage.usage_date', [
                $start->toDateString(),
                $end->toDateString(),
            ])
            ->select(
                'customers.id',
                'customers.name',
                DB::raw('SUM(daily_usage.total_units) as total_usage'),
                DB::raw('MAX(plans.included_units) as included_units')
            )
            ->groupBy(
                'customers.id',
                'customers.name'
            )
            ->orderByDesc('total_usage')
            ->limit(5)
            ->get()
            ->map(function ($row) {
                $usage = (int) $row->total_usage;
                $included = (int) $row->included_units;

                return [
                    'id' => $row->id,
                    'name' => $row->name,
                    'usage' => $usage,
                    'included_units' => $included,
                    'usage_percentage' => $included > 0
                        ? round(($usage / $included) * 100, 2)
                        : 0,
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Customers with more than 50% usage drop month over month.
     *
     * LEFT JOIN is important because a customer with zero
     * current usage should still be detected as a 100% drop.
     */
    private function usageDrops(
        Merchant $merchant,
        Carbon $currentStart,
        Carbon $currentEnd,
        Carbon $previousStart,
        Carbon $previousEnd
    ): array {
        $previous = DB::table('daily_usage')
            ->select(
                'customer_id',
                DB::raw('SUM(total_units) as previous_usage')
            )
            ->where('merchant_id', $merchant->id)
            ->whereBetween('usage_date', [
                $previousStart->toDateString(),
                $previousEnd->toDateString(),
            ])
            ->groupBy('customer_id');

        $current = DB::table('daily_usage')
            ->select(
                'customer_id',
                DB::raw('SUM(total_units) as current_usage')
            )
            ->where('merchant_id', $merchant->id)
            ->whereBetween('usage_date', [
                $currentStart->toDateString(),
                $currentEnd->toDateString(),
            ])
            ->groupBy('customer_id');

        return DB::table('customers')
            ->joinSub(
                $previous,
                'previous_usage',
                function ($join) {
                    $join->on(
                        'previous_usage.customer_id',
                        '=',
                        'customers.id'
                    );
                }
            )
            ->leftJoinSub(
                $current,
                'current_usage',
                function ($join) {
                    $join->on(
                        'current_usage.customer_id',
                        '=',
                        'customers.id'
                    );
                }
            )
            ->where('customers.merchant_id', $merchant->id)
            ->select(
                'customers.id',
                'customers.name',
                DB::raw(
                    'COALESCE(current_usage.current_usage, 0) as current_usage'
                ),
                'previous_usage.previous_usage'
            )
            ->get()
            ->map(function ($row) {
                $previousUsage = (int) $row->previous_usage;
                $currentUsage = (int) $row->current_usage;

                if ($previousUsage <= 0) {
                    return null;
                }

                $dropPercentage =
                    (($previousUsage - $currentUsage)
                        / $previousUsage) * 100;

                if ($dropPercentage <= 50) {
                    return null;
                }

                return [
                    'id' => $row->id,
                    'name' => $row->name,
                    'current_usage' => $currentUsage,
                    'previous_usage' => $previousUsage,
                    'drop_percentage' => round($dropPercentage, 2),
                ];
            })
            ->filter()
            ->sortByDesc('drop_percentage')
            ->values()
            ->toArray();
    }

    /**
     * Last 30 days usage.
     */
    private function dailyUsageTrend(
        Merchant $merchant,
        Carbon $start,
        Carbon $end
    ): array {
        $rows = DB::table('daily_usage')
            ->where('merchant_id', $merchant->id)
            ->whereBetween('usage_date', [
                $start->toDateString(),
                $end->toDateString(),
            ])
            ->select(
                'usage_date',
                DB::raw('SUM(total_units) as total_usage')
            )
            ->groupBy('usage_date')
            ->orderBy('usage_date')
            ->get()
            ->keyBy(function ($row) {
                return Carbon::parse($row->usage_date)->toDateString();
            });

        $result = [];

        $date = $start->copy();

        while ($date->lte($end)) {
            $dateKey = $date->toDateString();

            $result[] = [
                'date' => $dateKey,
                'label' => $date->format('d M'),
                'usage' => isset($rows[$dateKey])
                    ? (int) $rows[$dateKey]->total_usage
                    : 0,
            ];

            $date->addDay();
        }

        return $result;
    }
}
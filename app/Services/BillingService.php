<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BillingService
{
    public function generateInvoice(
        Customer $customer,
        Carbon $periodStart,
        Carbon $periodEnd
    ): Invoice {
        return DB::transaction(function () use (
            $customer,
            $periodStart,
            $periodEnd
        ) {
            $invoice = Invoice::create([
                'merchant_id' => $customer->merchant_id,
                'customer_id' => $customer->id,
                'invoice_number' => $this->generateInvoiceNumber(),
                'billing_period_start' => $periodStart->toDateString(),
                'billing_period_end' => $periodEnd->toDateString(),
                'subtotal' => 0,
                'total' => 0,
                'status' => 'draft',
            ]);

            $subscriptions = $customer->subscriptions()
                ->with('plan')
                ->where('starts_at', '<', $periodEnd->copy()->addDay())
                ->where(function ($query) use ($periodStart) {
                    $query->whereNull('ends_at')
                        ->orWhere('ends_at', '>', $periodStart);
                })
                ->orderBy('starts_at')
                ->get();

            $subtotal = 0;

            foreach ($subscriptions as $subscription) {
                $segmentStart = $this->segmentStart(
                    $subscription,
                    $periodStart
                );

                $segmentEnd = $this->segmentEnd(
                    $subscription,
                    $periodEnd
                );

                if ($segmentStart->gt($segmentEnd)) {
                    continue;
                }

                $segment = $this->calculateSegment(
                    $subscription,
                    $segmentStart,
                    $segmentEnd
                );

                if ($segment['base_amount'] > 0) {
                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'plan_id' => $subscription->plan_id,
                        'type' => 'base',
                        'description' => sprintf(
                            '%s plan (%s to %s)',
                            $subscription->plan->name,
                            $segmentStart->toDateString(),
                            $segmentEnd->toDateString()
                        ),
                        'quantity' => $segment['days'],
                        'unit_price' => $segment['daily_rate'],
                        'amount' => $segment['base_amount'],
                    ]);
                }

                if ($segment['overage_amount'] > 0) {
                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'plan_id' => $subscription->plan_id,
                        'type' => 'overage',
                        'description' => sprintf(
                            '%s overage',
                            $subscription->plan->name
                        ),
                        'quantity' => $segment['overage_units'],
                        'unit_price' => $subscription->plan->overage_rate,
                        'amount' => $segment['overage_amount'],
                    ]);
                }

                $subtotal += $segment['base_amount'];
                $subtotal += $segment['overage_amount'];
            }

            $invoice->update([
                'subtotal' => $subtotal,
                'total' => $subtotal,
            ]);

            return $invoice->fresh('items');
        });
    }

    private function calculateSegment(
        Subscription $subscription,
        Carbon $start,
        Carbon $end
    ): array {
        $plan = $subscription->plan;

        $days = $start->diffInDays($end) + 1;

        $cycleDays = $this->billingCycleDays(
            $start,
            $plan->billing_cycle
        );

        $dailyRate = $plan->base_price / $cycleDays;

        $baseAmount = $dailyRate * $days;

        $proratedIncludedUnits =
            $plan->included_units * ($days / $cycleDays);

        $usageUnits = $this->usageForSegment(
            $subscription,
            $start,
            $end
        );

        $overageUnits = max(
            0,
            $usageUnits - $proratedIncludedUnits
        );

        $overageAmount =
            $overageUnits * $plan->overage_rate;

        return [
            'days' => $days,
            'cycle_days' => $cycleDays,
            'daily_rate' => round($dailyRate, 6),
            'base_amount' => round($baseAmount, 2),
            'included_units' => round($proratedIncludedUnits, 4),
            'usage_units' => $usageUnits,
            'overage_units' => round($overageUnits, 4),
            'overage_amount' => round($overageAmount, 2),
        ];
    }

    private function usageForSegment(
        Subscription $subscription,
        Carbon $start,
        Carbon $end
    ): int {
        return (int) $subscription->dailyUsage()
            ->whereBetween('usage_date', [
                $start->toDateString(),
                $end->toDateString(),
            ])
            ->sum('total_units');
    }

    private function segmentStart(
        Subscription $subscription,
        Carbon $periodStart
    ): Carbon {
        $start = Carbon::parse($subscription->starts_at);

        return $start->greaterThan($periodStart)
            ? $start->startOfDay()
            : $periodStart->copy()->startOfDay();
    }

    private function segmentEnd(
        Subscription $subscription,
        Carbon $periodEnd
    ): Carbon {
        if (!$subscription->ends_at) {
            return $periodEnd->copy()->endOfDay();
        }

        $end = Carbon::parse($subscription->ends_at)
            ->subSecond();

        return $end->lessThan($periodEnd)
            ? $end->endOfDay()
            : $periodEnd->copy()->endOfDay();
    }

    private function billingCycleDays(
        Carbon $start,
        string $billingCycle
    ): int {
        if ($billingCycle === 'yearly') {
            return $start->isLeapYear() ? 366 : 365;
        }

        return $start->daysInMonth;
    }

    private function generateInvoiceNumber(): string
    {
        return 'INV-' . now()->format('Ym') . '-' .
            strtoupper(Str::random(8));
    }
}
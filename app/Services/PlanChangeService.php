<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionChange;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use DomainException;

class PlanChangeService
{
    public function change(
        Customer $customer,
        Plan $newPlan,
        Carbon $effectiveAt
    ): Subscription {
        return DB::transaction(function () use (
            $customer,
            $newPlan,
            $effectiveAt
        ) {
            $effectiveAt = $effectiveAt->copy();

            /*
             * Find the subscription that is active at the
             * requested effective time.
             */
            $currentSubscription = $customer->subscriptions()
                ->where('status', 'active')
                ->where('starts_at', '<=', $effectiveAt)
                ->where(function ($query) use ($effectiveAt) {
                    $query->whereNull('ends_at')
                        ->orWhere('ends_at', '>', $effectiveAt);
                })
                ->with('plan')
                ->lockForUpdate()
                ->first();

            if (!$currentSubscription) {
                throw new DomainException(
                    'Customer does not have an active subscription at the requested time.'
                );
            }

            if ($currentSubscription->plan_id === $newPlan->id) {
                throw new DomainException(
                    'Customer is already subscribed to this plan.'
                );
            }

            if ($newPlan->merchant_id !== $customer->merchant_id) {
                throw new DomainException(
                    'The new plan does not belong to the customer merchant.'
                );
            }

            /*
             * Only allow changes after the current subscription starts.
             */
            if (
                $effectiveAt->lessThanOrEqualTo(
                    Carbon::parse($currentSubscription->starts_at)
                )
            ) {
                throw new DomainException(
                    'Plan change must happen after the current subscription starts.'
                );
            }

            /*
             * End the old subscription segment.
             *
             * UsageService uses:
             *
             * starts_at <= occurred_at
             * AND ends_at > occurred_at
             *
             * Therefore the effective time belongs to the NEW segment.
             */
            $currentSubscription->update([
                'ends_at' => $effectiveAt,
            ]);

            /*
             * Create the new subscription segment.
             */
            $newSubscription = Subscription::create([
                'customer_id' => $customer->id,
                'plan_id' => $newPlan->id,
                'starts_at' => $effectiveAt,
                'ends_at' => null,
                'status' => 'active',
            ]);

            /*
             * Record the plan change for auditing.
             */
            SubscriptionChange::create([
                'subscription_id' => $newSubscription->id,
                'old_plan_id' => $currentSubscription->plan_id,
                'new_plan_id' => $newPlan->id,
                'effective_at' => $effectiveAt,
                'change_type' => $this->changeType(
                    $currentSubscription->plan,
                    $newPlan
                ),
            ]);

            return $newSubscription->fresh([
                'plan',
                'customer',
            ]);
        });
    }

    private function changeType(
        Plan $oldPlan,
        Plan $newPlan
    ): string {
        if ($newPlan->base_price > $oldPlan->base_price) {
            return 'upgrade';
        }

        return 'downgrade';
    }
}
<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\UsageEvent;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class UsageService
{
    public function record(
        Customer $customer,
        int $quantity,
        Carbon $occurredAt,
        string $idempotencyKey
    ): UsageEvent {
        $subscription = $customer->subscriptions()
            ->where('status', 'active')
            ->where('starts_at', '<=', $occurredAt)
            ->where(function ($query) use ($occurredAt) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>', $occurredAt);
            })
            ->with('plan')
            ->first();

        if (!$subscription) {
            throw new \DomainException(
                'Customer does not have an active subscription for this usage date.'
            );
        }

        try {
            return DB::transaction(function () use (
                $customer,
                $subscription,
                $quantity,
                $occurredAt,
                $idempotencyKey
            ) {
                return UsageEvent::create([
                    'merchant_id' => $customer->merchant_id,
                    'customer_id' => $customer->id,
                    'subscription_id' => $subscription->id,
                    'idempotency_key' => $idempotencyKey,
                    'usage_date' => $occurredAt->toDateString(),
                    'quantity' => $quantity,
                    'occurred_at' => $occurredAt,
                ]);
            });
        } catch (QueryException $exception) {
            if ($this->isDuplicateIdempotencyKey($exception)) {
                return UsageEvent::where('customer_id', $customer->id)
                    ->where('idempotency_key', $idempotencyKey)
                    ->firstOrFail();
            }

            return $exception;
        }
    }

    private function isDuplicateIdempotencyKey(
        QueryException $exception
    ): bool {
        return $exception->getCode() === '23000';
    }
}
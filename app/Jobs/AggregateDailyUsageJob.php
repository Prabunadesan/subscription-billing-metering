<?php

namespace App\Jobs;

use App\Models\DailyUsage;
use App\Models\UsageEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AggregateDailyUsageJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Maximum time the job can run.
     */
    public int $timeout = 300;

    /**
     * Number of retry attempts.
     */
    public int $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public readonly int $merchantId,
        public readonly string $usageDate,
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        /*
         * Aggregate raw usage events in MySQL.
         *
         * Important:
         * Do not use chunk() directly on this GROUP BY query.
         * Laravel's chunk() adds ORDER BY usage_events.id,
         * which conflicts with MySQL ONLY_FULL_GROUP_BY.
         */
        $rows = UsageEvent::query()
            ->select([
                'merchant_id',
                'customer_id',
                'subscription_id',
                'usage_date',
            ])
            ->selectRaw('SUM(quantity) as total_units')
            ->where('merchant_id', $this->merchantId)
            ->where('usage_date', $this->usageDate)
            ->groupBy(
                'merchant_id',
                'customer_id',
                'subscription_id',
                'usage_date'
            )
            ->get();

        if ($rows->isEmpty()) {
            return;
        }

        /*
         * Prepare rows for daily_usage.
         */
        $data = $rows->map(function ($row) {
            return [
                'merchant_id' => $row->merchant_id,
                'customer_id' => $row->customer_id,
                'subscription_id' => $row->subscription_id,
                'usage_date' => $row->usage_date,
                'total_units' => $row->total_units,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        })->all();

        /*
         * Upsert makes the aggregation retry-safe.
         *
         * If the same job runs again:
         *
         * 10 + 25 = 35
         *
         * It updates the existing daily total to 35.
         * It does NOT add another 35.
         */
        DailyUsage::upsert(
            $data,
            [
                'customer_id',
                'subscription_id',
                'usage_date',
            ],
            [
                'merchant_id',
                'total_units',
                'updated_at',
            ]
        );
    }
}
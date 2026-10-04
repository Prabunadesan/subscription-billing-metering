<?php

namespace App\Observers;

use App\Models\Plan;
use Illuminate\Support\Facades\Cache;

class PlanObserver
{
    public function updated(Plan $plan): void
    {
        $this->forget($plan);
    }

    public function deleted(Plan $plan): void
    {
        $this->forget($plan);
    }

    private function forget(Plan $plan): void
    {
        Cache::forget("plan:pricing:{$plan->id}");
    }
}
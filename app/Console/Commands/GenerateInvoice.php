<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Services\BillingService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateInvoice extends Command
{
    protected $signature = 'billing:generate
                            {customer_id : Customer ID}
                            {start : Billing period start YYYY-MM-DD}
                            {end : Billing period end YYYY-MM-DD}';

    protected $description = 'Generate an invoice for a customer';

    public function handle(BillingService $billingService): int
    {
        $customer = Customer::findOrFail(
            $this->argument('customer_id')
        );

        $start = Carbon::parse(
            $this->argument('start')
        )->startOfDay();

        $end = Carbon::parse(
            $this->argument('end')
        )->startOfDay();

        $invoice = $billingService->generateInvoice(
            $customer,
            $start,
            $end
        );

        $this->info(
            "Invoice generated: {$invoice->invoice_number}"
        );

        $this->info(
            "Total: {$invoice->total}"
        );

        return self::SUCCESS;
    }
}
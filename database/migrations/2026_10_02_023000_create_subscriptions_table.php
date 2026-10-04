<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('customer_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('plan_id')
                ->constrained()
                ->restrictOnDelete();

            $table->dateTime('starts_at');
            $table->dateTime('ends_at')->nullable();

            $table->enum('status', [
                'active',
                'cancelled',
                'expired',
            ])->default('active');

            $table->timestamps();

            $table->index([
                'customer_id',
                'starts_at',
                'ends_at',
            ]);

            $table->index([
                'customer_id',
                'status',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};

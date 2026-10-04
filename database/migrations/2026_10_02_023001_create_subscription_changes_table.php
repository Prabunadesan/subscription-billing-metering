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
        Schema::create('subscription_changes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('subscription_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('old_plan_id')
                ->nullable()
                ->constrained('plans')
                ->nullOnDelete();

            $table->foreignId('new_plan_id')
                ->constrained('plans')
                ->restrictOnDelete();

            $table->dateTime('effective_at');

            $table->enum('change_type', [
                'upgrade',
                'downgrade',
            ]);

            $table->timestamps();

            $table->index([
                'subscription_id',
                'effective_at',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_changes');
    }
};

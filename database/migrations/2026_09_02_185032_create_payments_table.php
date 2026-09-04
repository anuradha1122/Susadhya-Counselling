<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'payments',
            function (Blueprint $table): void {
                $table->id();

                $table->uuid('uuid')->unique();

                $table->foreignId(
                    'appointment_id'
                )
                    ->constrained()
                    ->restrictOnDelete();

                $table->foreignId(
                    'client_profile_id'
                )
                    ->constrained()
                    ->restrictOnDelete();

                $table->string(
                    'provider',
                    50
                );

                $table->string(
                    'method',
                    50
                );

                $table->string(
                    'provider_payment_id'
                )->nullable();

                $table->string(
                    'provider_reference'
                )->nullable();

                $table->uuid(
                    'idempotency_key'
                )->unique();

                $table->decimal(
                    'amount',
                    12,
                    2
                );

                $table->char(
                    'currency',
                    3
                );

                $table->string(
                    'status',
                    40
                )->default('pending');

                $table->string(
                    'provider_status',
                    60
                )->nullable();

                $table->decimal(
                    'provider_amount',
                    12,
                    2
                )->nullable();

                $table->char(
                    'provider_currency',
                    3
                )->nullable();

                $table->string(
                    'reconciliation_status',
                    40
                )->default('pending');

                $table->timestamp(
                    'provider_synced_at'
                )->nullable();

                $table->timestamp(
                    'paid_at'
                )->nullable();

                $table->timestamp(
                    'failed_at'
                )->nullable();

                $table->timestamp(
                    'reconciled_at'
                )->nullable();

                $table->string(
                    'failure_code',
                    100
                )->nullable();

                $table->text(
                    'failure_message'
                )->nullable();

                $table->json(
                    'metadata'
                )->nullable();

                $table->foreignId(
                    'created_by'
                )
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->foreignId(
                    'updated_by'
                )
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->timestamps();

                $table->index([
                    'appointment_id',
                    'status',
                ]);

                $table->index([
                    'client_profile_id',
                    'status',
                ]);

                $table->index([
                    'provider',
                    'provider_payment_id',
                ]);

                $table->index([
                    'reconciliation_status',
                    'status',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};

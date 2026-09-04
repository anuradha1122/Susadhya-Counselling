<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'refunds',
            function (Blueprint $table): void {
                $table->id();
                $table->uuid('uuid')->unique();

                $table->foreignId(
                    'payment_id'
                )
                    ->constrained()
                    ->restrictOnDelete();

                $table->string(
                    'refund_number',
                    50
                )->unique();

                $table->foreignId(
                    'requested_by'
                )
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->decimal(
                    'requested_amount',
                    12,
                    2
                );

                $table->decimal(
                    'approved_amount',
                    12,
                    2
                )->nullable();

                $table->char(
                    'currency',
                    3
                );

                $table->text('reason');

                $table->string(
                    'status',
                    40
                )->default('requested');

                $table->text(
                    'decision_notes'
                )->nullable();

                $table->foreignId(
                    'decided_by'
                )
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->string(
                    'provider_refund_id'
                )->nullable();

                $table->string(
                    'provider_reference'
                )->nullable();

                $table->timestamp(
                    'requested_at'
                );

                $table->timestamp(
                    'decided_at'
                )->nullable();

                $table->timestamp(
                    'processed_at'
                )->nullable();

                $table->timestamp(
                    'failed_at'
                )->nullable();

                $table->text(
                    'failure_message'
                )->nullable();

                $table->json(
                    'metadata'
                )->nullable();

                $table->timestamps();

                $table->index([
                    'payment_id',
                    'status',
                ]);

                $table->index([
                    'status',
                    'requested_at',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};

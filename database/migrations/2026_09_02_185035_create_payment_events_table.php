<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'payment_events',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId(
                    'payment_id'
                )
                    ->nullable()
                    ->constrained()
                    ->nullOnDelete();

                $table->foreignId(
                    'refund_id'
                )
                    ->nullable()
                    ->constrained()
                    ->nullOnDelete();

                $table->foreignId(
                    'actor_id'
                )
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->string(
                    'source',
                    40
                );

                $table->string(
                    'event_type',
                    80
                );

                $table->string(
                    'provider_event_id'
                )
                    ->nullable()
                    ->unique();

                $table->string(
                    'status',
                    40
                )->default('recorded');

                $table->ipAddress(
                    'ip_address'
                )->nullable();

                $table->text(
                    'user_agent'
                )->nullable();

                $table->json(
                    'metadata'
                )->nullable();

                $table->timestamp(
                    'created_at'
                )->useCurrent();

                $table->index([
                    'payment_id',
                    'created_at',
                ]);

                $table->index([
                    'refund_id',
                    'created_at',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'payment_events'
        );
    }
};

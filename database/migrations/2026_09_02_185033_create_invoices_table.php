<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'invoices',
            function (Blueprint $table): void {
                $table->id();
                $table->uuid('uuid')->unique();

                $table->foreignId(
                    'payment_id'
                )
                    ->unique()
                    ->constrained()
                    ->restrictOnDelete();

                $table->string(
                    'invoice_number',
                    50
                )->unique();

                $table->string(
                    'receipt_number',
                    50
                )
                    ->nullable()
                    ->unique();

                $table->decimal(
                    'subtotal',
                    12,
                    2
                );

                $table->decimal(
                    'total',
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
                )->default('issued');

                $table->string(
                    'billing_name'
                );

                $table->string(
                    'billing_email'
                )->nullable();

                $table->timestamp(
                    'issued_at'
                );

                $table->timestamp(
                    'paid_at'
                )->nullable();

                $table->timestamp(
                    'receipt_issued_at'
                )->nullable();

                $table->json(
                    'metadata'
                )->nullable();

                $table->timestamps();

                $table->index([
                    'status',
                    'issued_at',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};

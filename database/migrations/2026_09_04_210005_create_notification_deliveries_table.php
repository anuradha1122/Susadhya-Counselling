<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_deliveries', function (Blueprint $table) {
            $table->id();

            $table->foreignUuid('notification_dispatch_id')
                ->constrained('notification_dispatches')
                ->cascadeOnDelete();

            $table->string('channel', 30);

            $table->string('status', 30)->default('pending');

            $table->unsignedSmallInteger('attempt')->default(1);

            $table->timestamp('attempted_at')->nullable();

            $table->timestamp('sent_at')->nullable();

            $table->timestamp('delivered_at')->nullable();

            $table->timestamp('failed_at')->nullable();

            $table->string('provider_message_id')->nullable();

            $table->text('error_message')->nullable();

            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->unique([
                'notification_dispatch_id',
                'channel',
                'attempt',
            ], 'notification_delivery_attempt_unique');

            $table->index([
                'channel',
                'status',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_deliveries');
    }
};

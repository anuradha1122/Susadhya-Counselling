<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_dispatches', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('event_type', 100);

            $table->string('template_key', 150);

            $table->json('channels')->nullable();

            $table->string('source_type')->nullable();

            $table->string('source_id', 100)->nullable();

            $table->string('url', 1000)->nullable();

            $table->json('payload')->nullable();

            $table->string('deduplication_key', 191)->unique();

            $table->timestamp('dispatched_at')->nullable();

            $table->timestamps();

            $table->index([
                'user_id',
                'event_type',
            ]);

            $table->index([
                'source_type',
                'source_id',
            ]);

            $table->index('template_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_dispatches');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'privacy_request_events',
            function (Blueprint $table): void {
                $table->id();

                $table->uuid('uuid')
                    ->unique('pre_uuid_uq');

                $table->unsignedBigInteger(
                    'privacy_request_id'
                );

                $table->unsignedBigInteger(
                    'actor_id'
                )->nullable();

                $table->string('event', 120);

                $table->string('from_status', 40)
                    ->nullable();

                $table->string('to_status', 40)
                    ->nullable();

                $table->longText('notes')
                    ->nullable();

                $table->json('metadata')
                    ->nullable();

                $table->timestamp('created_at')
                    ->useCurrent();

                $table->foreign(
                    'privacy_request_id',
                    'pre_request_fk'
                )
                    ->references('id')
                    ->on('privacy_requests')
                    ->restrictOnDelete();

                $table->foreign(
                    'actor_id',
                    'pre_actor_fk'
                )
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();

                $table->index(
                    [
                        'privacy_request_id',
                        'created_at',
                    ],
                    'pre_request_created_idx'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'privacy_request_events'
        );
    }
};

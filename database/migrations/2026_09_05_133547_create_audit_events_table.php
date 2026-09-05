<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_events', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique('ae_uuid_uq');

            $table->unsignedBigInteger('actor_id')->nullable();

            $table->string('category', 80);
            $table->string('event', 160);
            $table->string('action', 160);

            $table->string('subject_type', 160)->nullable();
            $table->string('subject_id', 80)->nullable();
            $table->uuid('subject_uuid')->nullable();

            $table->string('result', 32)
                ->default('success');

            $table->string('purpose_code', 100)
                ->nullable();

            $table->text('reason')
                ->nullable();

            $table->json('metadata')
                ->nullable();

            $table->string('ip_address', 45)
                ->nullable();

            $table->text('user_agent')
                ->nullable();

            $table->string('request_id', 80)
                ->nullable();

            $table->timestamp('occurred_at')
                ->useCurrent();

            $table->timestamp('created_at')
                ->useCurrent();

            $table->foreign(
                'actor_id',
                'ae_actor_fk'
            )
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->index(
                ['category', 'occurred_at'],
                'ae_cat_occ_idx'
            );

            $table->index(
                ['actor_id', 'occurred_at'],
                'ae_actor_occ_idx'
            );

            $table->index(
                ['subject_type', 'subject_id'],
                'ae_subject_idx'
            );

            $table->index(
                ['event', 'occurred_at'],
                'ae_event_occ_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_events');
    }
};

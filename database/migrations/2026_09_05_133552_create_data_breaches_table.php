<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'data_breaches',
            function (Blueprint $table): void {
                $table->id();

                $table->uuid('uuid')
                    ->unique('db_uuid_uq');

                $table->string(
                    'reference',
                    40
                )->unique('db_ref_uq');

                $table->string('title', 180);

                $table->string(
                    'severity',
                    20
                )->default('medium');

                $table->string(
                    'status',
                    32
                )->default('open');

                $table->unsignedBigInteger(
                    'reported_by'
                )->nullable();

                $table->unsignedBigInteger(
                    'assigned_to'
                )->nullable();

                $table->timestamp(
                    'detected_at'
                );

                $table->timestamp(
                    'occurred_at'
                )->nullable();

                $table->timestamp(
                    'contained_at'
                )->nullable();

                $table->timestamp(
                    'reported_to_authority_at'
                )->nullable();

                $table->timestamp(
                    'closed_at'
                )->nullable();

                $table->unsignedInteger(
                    'affected_subject_count'
                )->nullable();

                $table->json(
                    'data_categories'
                )->nullable();

                $table->json(
                    'systems_affected'
                )->nullable();

                $table->longText('summary')
                    ->nullable();

                $table->longText(
                    'containment_actions'
                )->nullable();

                $table->longText(
                    'notification_decision'
                )->nullable();

                $table->string(
                    'authority_reference',
                    160
                )->nullable();

                $table->timestamps();

                $table->foreign(
                    'reported_by',
                    'db_reported_by_fk'
                )
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();

                $table->foreign(
                    'assigned_to',
                    'db_assigned_to_fk'
                )
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();

                $table->index(
                    ['status', 'severity'],
                    'db_status_sev_idx'
                );

                $table->index(
                    'detected_at',
                    'db_detected_idx'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'data_breaches'
        );
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'retention_runs',
            function (Blueprint $table): void {
                $table->id();

                $table->uuid('uuid')
                    ->unique('rr_uuid_uq');

                $table->unsignedBigInteger(
                    'retention_policy_id'
                );

                $table->unsignedBigInteger(
                    'initiated_by'
                )->nullable();

                $table->string('mode', 20)
                    ->default('dry_run');

                $table->string('status', 32)
                    ->default('running');

                $table->unsignedInteger(
                    'candidate_count'
                )->default(0);

                $table->unsignedInteger(
                    'processed_count'
                )->default(0);

                $table->unsignedInteger(
                    'skipped_count'
                )->default(0);

                $table->unsignedInteger(
                    'failed_count'
                )->default(0);

                $table->json('summary')
                    ->nullable();

                $table->timestamp('started_at')
                    ->useCurrent();

                $table->timestamp('completed_at')
                    ->nullable();

                $table->timestamps();

                $table->foreign(
                    'retention_policy_id',
                    'rr_policy_fk'
                )
                    ->references('id')
                    ->on('retention_policies')
                    ->restrictOnDelete();

                $table->foreign(
                    'initiated_by',
                    'rr_actor_fk'
                )
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();

                $table->index(
                    [
                        'retention_policy_id',
                        'started_at',
                    ],
                    'rr_policy_started_idx'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('retention_runs');
    }
};

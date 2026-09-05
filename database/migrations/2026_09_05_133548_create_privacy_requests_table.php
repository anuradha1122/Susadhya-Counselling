<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('privacy_requests', function (Blueprint $table): void {
            $table->id();

            $table->uuid('uuid')
                ->unique('pr_uuid_uq');

            $table->unsignedBigInteger(
                'requester_user_id'
            )->nullable();

            $table->unsignedBigInteger(
                'subject_user_id'
            )->nullable();

            $table->string('type', 32);

            $table->string('status', 40)
                ->default('submitted');

            $table->longText('request_details')
                ->nullable();

            $table->json('scope')
                ->nullable();

            $table->timestamp('submitted_at')
                ->useCurrent();

            $table->timestamp('identity_verified_at')
                ->nullable();

            $table->unsignedBigInteger(
                'identity_verified_by'
            )->nullable();

            $table->timestamp('reviewed_at')
                ->nullable();

            $table->unsignedBigInteger(
                'reviewed_by'
            )->nullable();

            $table->string('decision', 32)
                ->nullable();

            $table->longText('review_notes')
                ->nullable();

            $table->text('legal_basis')
                ->nullable();

            $table->timestamp('due_at')
                ->nullable();

            $table->string('export_disk', 40)
                ->nullable();

            $table->string('export_path', 500)
                ->nullable();

            $table->char('export_checksum', 64)
                ->nullable();

            $table->timestamp('export_prepared_at')
                ->nullable();

            $table->string('deletion_strategy', 80)
                ->nullable();

            $table->longText('execution_notes')
                ->nullable();

            $table->timestamp('completed_at')
                ->nullable();

            $table->unsignedBigInteger(
                'completed_by'
            )->nullable();

            $table->timestamps();

            $table->foreign(
                'requester_user_id',
                'pr_requester_fk'
            )
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign(
                'subject_user_id',
                'pr_subject_fk'
            )
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign(
                'identity_verified_by',
                'pr_verified_by_fk'
            )
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign(
                'reviewed_by',
                'pr_reviewed_by_fk'
            )
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign(
                'completed_by',
                'pr_completed_by_fk'
            )
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->index(
                ['status', 'submitted_at'],
                'pr_status_sub_idx'
            );

            $table->index(
                ['subject_user_id', 'type'],
                'pr_subject_type_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('privacy_requests');
    }
};

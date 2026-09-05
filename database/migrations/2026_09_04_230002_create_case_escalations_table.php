<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'case_escalations',
            function (Blueprint $table): void {
                $table->id();
                $table->uuid('uuid')->unique('case_esc_uuid_unique');

                /*
                 * Operational reference only.
                 *
                 * Deliberately not an FK to client_cases. Ordinary Admin
                 * does not receive clinical-record access through M16.
                 */
                $table->string('case_reference', 100);

                $table->string('reason_code', 60);
                $table->string('priority', 20)->default('medium');
                $table->string('status', 30)->default('open');

                $table->unsignedBigInteger('assigned_to')->nullable();

                $table->timestamp('due_at')->nullable();

                /*
                 * Administrative metadata only.
                 * Never store clinical narratives here.
                 */
                $table->text('admin_note')->nullable();
                $table->text('resolution_notes')->nullable();

                $table->unsignedBigInteger('resolved_by')->nullable();
                $table->timestamp('resolved_at')->nullable();

                $table->unsignedBigInteger('created_by');
                $table->unsignedBigInteger('updated_by')->nullable();

                $table->timestamps();

                $table
                    ->foreign('assigned_to', 'case_esc_assigned_fk')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();

                $table
                    ->foreign('resolved_by', 'case_esc_resolved_fk')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();

                $table
                    ->foreign('created_by', 'case_esc_created_fk')
                    ->references('id')
                    ->on('users')
                    ->restrictOnDelete();

                $table
                    ->foreign('updated_by', 'case_esc_updated_fk')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();

                $table->index(
                    ['status', 'priority'],
                    'case_esc_status_priority_idx'
                );

                $table->index(
                    ['assigned_to', 'status'],
                    'case_esc_assignee_idx'
                );

                $table->index(
                    'case_reference',
                    'case_esc_reference_idx'
                );

                $table->index(
                    ['status', 'due_at'],
                    'case_esc_due_idx'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('case_escalations');
    }
};

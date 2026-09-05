<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'operational_exceptions',
            function (Blueprint $table): void {
                $table->id();
                $table->uuid('uuid')->unique('op_exc_uuid_unique');

                $table->string('type', 50);
                $table->string('priority', 20)->default('medium');
                $table->string('status', 30)->default('open');

                $table->string('source_type', 50)->nullable();
                $table->string('source_reference', 100)->nullable();

                $table->string('title', 180);
                $table->text('description')->nullable();

                $table->unsignedBigInteger('assigned_to')->nullable();

                $table->timestamp('due_at')->nullable();

                $table->text('resolution_notes')->nullable();

                $table->unsignedBigInteger('resolved_by')->nullable();
                $table->timestamp('resolved_at')->nullable();

                $table->unsignedBigInteger('created_by');
                $table->unsignedBigInteger('updated_by')->nullable();

                $table->timestamps();

                $table
                    ->foreign('assigned_to', 'op_exc_assigned_fk')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();

                $table
                    ->foreign('resolved_by', 'op_exc_resolved_fk')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();

                $table
                    ->foreign('created_by', 'op_exc_created_fk')
                    ->references('id')
                    ->on('users')
                    ->restrictOnDelete();

                $table
                    ->foreign('updated_by', 'op_exc_updated_fk')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();

                $table->index(
                    ['status', 'priority'],
                    'op_exc_status_priority_idx'
                );

                $table->index(
                    ['source_type', 'source_reference'],
                    'op_exc_source_idx'
                );

                $table->index(
                    ['assigned_to', 'status'],
                    'op_exc_assignee_status_idx'
                );

                $table->index(
                    ['status', 'due_at'],
                    'op_exc_due_idx'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('operational_exceptions');
    }
};

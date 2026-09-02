<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'document_access_logs',
            function (Blueprint $table): void {
                $table->id();

                $table
                    ->foreignId('actor_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table
                    ->foreignId(
                        'secure_document_id'
                    )
                    ->nullable()
                    ->constrained(
                        'secure_documents'
                    )
                    ->nullOnDelete();

                $table->uuid(
                    'document_uuid'
                );

                $table->string(
                    'action',
                    40
                );

                $table
                    ->string(
                        'ip_address',
                        64
                    )
                    ->nullable();

                $table
                    ->text('user_agent')
                    ->nullable();

                $table
                    ->json('metadata')
                    ->nullable();

                $table
                    ->timestamp('created_at')
                    ->useCurrent();

                $table->index(
                    [
                        'secure_document_id',
                        'created_at',
                    ],
                    'document_access_document_date_idx'
                );

                $table->index(
                    [
                        'actor_id',
                        'created_at',
                    ],
                    'document_access_actor_date_idx'
                );

                $table->index(
                    [
                        'document_uuid',
                        'action',
                    ],
                    'document_access_uuid_action_idx'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'document_access_logs'
        );
    }
};

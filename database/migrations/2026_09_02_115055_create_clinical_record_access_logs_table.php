<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'clinical_record_access_logs',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId('actor_id')
                    ->constrained('users')
                    ->restrictOnDelete();

                $table->foreignId('client_case_id')
                    ->constrained('client_cases')
                    ->cascadeOnDelete();

                $table->string('record_type', 80);
                $table->unsignedBigInteger('record_id')->nullable();

                $table->string('action', 80);

                $table->string('ip_address', 45)->nullable();
                $table->string('user_agent', 1000)->nullable();

                $table->json('metadata')->nullable();

                $table->timestamp('created_at')
                    ->useCurrent();

                $table->index([
                    'client_case_id',
                    'created_at',
                ]);

                $table->index([
                    'actor_id',
                    'created_at',
                ]);

                $table->index([
                    'record_type',
                    'record_id',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'clinical_record_access_logs'
        );
    }
};

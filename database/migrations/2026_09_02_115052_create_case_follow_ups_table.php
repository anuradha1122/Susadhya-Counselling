<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'case_follow_ups',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId('client_case_id')
                    ->constrained('client_cases')
                    ->cascadeOnDelete();

                $table->text('description');

                $table->timestamp('due_at')->nullable();

                $table->string('status', 30)
                    ->default('pending');

                $table->foreignId('created_by')
                    ->constrained('users')
                    ->restrictOnDelete();

                $table->foreignId('completed_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->timestamp('completed_at')->nullable();

                $table->timestamps();

                $table->index([
                    'client_case_id',
                    'status',
                ]);

                $table->index('due_at');
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('case_follow_ups');
    }
};

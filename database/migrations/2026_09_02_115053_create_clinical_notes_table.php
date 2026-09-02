<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinical_notes', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('client_case_id')
                ->constrained('client_cases')
                ->cascadeOnDelete();

            $table->foreignId('counselling_session_id')
                ->nullable()
                ->constrained('counselling_sessions')
                ->nullOnDelete();

            $table->foreignId('author_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->string('title')->nullable();

            $table->text('note');
            $table->text('formulation')->nullable();
            $table->text('intervention')->nullable();
            $table->text('risk_assessment')->nullable();
            $table->text('plan')->nullable();

            $table->string('risk_level', 20)
                ->default('low');

            $table->string('status', 20)
                ->default('draft');

            $table->unsignedInteger('version')
                ->default(1);

            $table->foreignId('signed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('signed_at')->nullable();
            $table->timestamp('locked_at')->nullable();

            $table->timestamps();

            $table->index([
                'client_case_id',
                'status',
            ]);

            $table->index([
                'author_id',
                'created_at',
            ]);

            $table->index('risk_level');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinical_notes');
    }
};

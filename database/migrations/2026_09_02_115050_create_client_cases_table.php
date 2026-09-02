<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_cases', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('client_profile_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('counsellor_profile_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('opened_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->string('status', 30)->default('open');

            $table->text('summary')->nullable();
            $table->text('formulation')->nullable();

            $table->string('risk_level', 20)->default('low');
            $table->boolean('risk_flag')->default(false);
            $table->text('risk_notes')->nullable();

            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();

            $table->foreignId('closed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index([
                'counsellor_profile_id',
                'status',
            ]);

            $table->index([
                'client_profile_id',
                'status',
            ]);

            $table->index([
                'risk_level',
                'risk_flag',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_cases');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('case_goals', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('client_case_id')
                ->constrained('client_cases')
                ->cascadeOnDelete();

            $table->text('description');
            $table->date('target_date')->nullable();

            $table->string('status', 30)
                ->default('active');

            $table->text('outcome_note')->nullable();

            $table->foreignId('created_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->index([
                'client_case_id',
                'status',
            ]);

            $table->index('target_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('case_goals');
    }
};

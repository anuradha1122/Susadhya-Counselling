<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_preferences', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('client_profile_id')
                ->unique()
                ->constrained()
                ->cascadeOnDelete();

            $table->enum('preferred_counselling_mode', [
                'online',
                'in_person',
                'no_preference',
            ])->default('no_preference');

            $table->enum('preferred_counsellor_gender', [
                'male',
                'female',
                'no_preference',
            ])->default('no_preference');

            $table->string('preferred_language', 40)->nullable();

            $table->text('general_availability_notes')->nullable();
            $table->text('accessibility_requirements')->nullable();
            $table->text('additional_preferences')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index([
                'preferred_counselling_mode',
                'preferred_counsellor_gender',
            ], 'client_preferences_mode_gender_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_preferences');
    }
};

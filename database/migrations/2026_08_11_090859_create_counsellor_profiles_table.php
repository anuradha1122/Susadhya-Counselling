<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('counsellor_profiles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->unique()
                ->constrained()
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->string('registration_number')->unique();
            $table->string('professional_title')->nullable();
            $table->string('nic', 20)->nullable()->unique();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', [
                'male',
                'female',
                'other',
                'prefer_not_to_say',
            ])->nullable();

            $table->unsignedSmallInteger('years_of_experience')
                ->default(0);

            $table->text('biography')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();

            $table->enum('status', [
                'active',
                'inactive',
                'archived',
            ])->default('active')->index();

            $table->timestamp('archived_at')->nullable();
            $table->foreignId('archived_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('counsellor_profiles');
    }
};

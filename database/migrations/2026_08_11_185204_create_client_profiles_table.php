<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_profiles', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('user_id')
                ->unique()
                ->constrained()
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->string('first_name', 120);
            $table->string('last_name', 120);
            $table->string('preferred_name', 120)->nullable();

            $table->date('date_of_birth')->nullable();

            $table->enum('gender', [
                'male',
                'female',
                'non_binary',
                'other',
                'prefer_not_to_say',
            ])->nullable();

            $table->string('pronouns', 80)->nullable();

            $table->string('alternate_phone', 30)->nullable();

            $table->text('address_line_1')->nullable();
            $table->text('address_line_2')->nullable();
            $table->string('city', 120)->nullable();
            $table->string('district', 120)->nullable();
            $table->string('province', 120)->nullable();
            $table->string('postal_code', 20)->nullable();

            $table->string('preferred_language', 40)->nullable();

            $table->enum('preferred_contact_method', [
                'email',
                'phone',
                'sms',
                'whatsapp',
                'no_preference',
            ])->default('email');

            $table->string('occupation', 150)->nullable();

            $table->enum('marital_status', [
                'single',
                'married',
                'separated',
                'divorced',
                'widowed',
                'other',
                'prefer_not_to_say',
            ])->nullable();

            $table->timestamp('profile_completed_at')->nullable();

            $table->timestamp('terms_accepted_at')->nullable();
            $table->timestamp('privacy_policy_accepted_at')->nullable();

            $table->boolean('communication_consent')
                ->default(false);

            $table->timestamp('communication_consent_at')->nullable();

            $table->boolean('emergency_contact_permission')
                ->default(false);

            $table->timestamp('emergency_contact_permission_at')->nullable();

            $table->json('privacy_preferences')->nullable();

            $table->enum('status', [
                'active',
                'inactive',
                'archived',
            ])->default('active')->index();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('archived_at')->nullable();

            $table->foreignId('archived_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index([
                'status',
                'profile_completed_at',
            ], 'client_profiles_status_completed_idx');

            $table->index([
                'preferred_language',
                'preferred_contact_method',
            ], 'client_profiles_lang_contact_idx');

            $table->index([
                'city',
                'district',
                'province',
            ], 'client_profiles_location_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_profiles');
    }
};

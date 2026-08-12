<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_consents', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('client_profile_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->enum('consent_type', [
                'terms',
                'privacy_policy',
                'communication',
                'emergency_contact',
            ]);

            $table->string('version', 50);
            $table->timestamp('accepted_at');

            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('metadata')->nullable();

            $table->foreignId('recorded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index([
                'client_profile_id',
                'consent_type',
                'version',
            ]);

            $table->index([
                'consent_type',
                'accepted_at',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_consents');
    }
};

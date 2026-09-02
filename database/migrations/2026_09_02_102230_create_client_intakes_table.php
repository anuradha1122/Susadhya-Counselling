<?php

use App\Models\ClientIntake;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_intakes', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('client_profile_id');

            $table->string('status')->default(ClientIntake::STATUS_DRAFT);

            $table->text('presenting_concerns')->nullable();
            $table->text('current_symptoms')->nullable();
            $table->text('counselling_goals')->nullable();

            $table->string('preferred_session_mode')->nullable();

            $table->boolean('previous_counselling')->nullable();
            $table->text('previous_counselling_notes')->nullable();

            $table->text('medication_notes')->nullable();

            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->string('emergency_contact_relationship')->nullable();

            $table->boolean('consent_terms_accepted')->default(false);
            $table->boolean('consent_privacy_accepted')->default(false);
            $table->boolean('consent_telehealth_accepted')->default(false);
            $table->boolean('consent_data_processing_accepted')->default(false);
            $table->timestamp('consent_given_at')->nullable();
            $table->string('consent_version')->default('MVP-1.0');

            $table->string('risk_level')->default(ClientIntake::RISK_LOW);
            $table->text('risk_notes')->nullable();

            $table->timestamp('submitted_at')->nullable();

            $table->foreignId('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('reviewer_notes')->nullable();

            $table->timestamps();

            $table->unique('client_profile_id', 'client_intakes_client_unique');
            $table->index(['status', 'risk_level'], 'client_intakes_status_risk_idx');
            $table->index('submitted_at', 'client_intakes_submitted_idx');

            $table
                ->foreign('client_profile_id', 'client_intakes_client_fk')
                ->references('id')
                ->on('client_profiles')
                ->restrictOnDelete();

            $table
                ->foreign('reviewed_by', 'client_intakes_reviewer_fk')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_intakes');
    }
};

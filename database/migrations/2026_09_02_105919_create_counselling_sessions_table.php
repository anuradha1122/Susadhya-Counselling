<?php

use App\Models\CounsellingSession;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('counselling_sessions', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('appointment_id');
            $table->foreignId('client_profile_id');
            $table->foreignId('counsellor_profile_id');

            $table->string('status')->default(CounsellingSession::STATUS_DRAFT);
            $table->string('mode')->nullable();

            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->text('presenting_summary')->nullable();
            $table->text('intervention_summary')->nullable();
            $table->text('outcome_summary')->nullable();
            $table->text('client_visible_summary')->nullable();
            $table->text('homework')->nullable();
            $table->text('private_notes')->nullable();
            $table->text('admin_notes')->nullable();

            $table->string('clinical_risk_level')->default(CounsellingSession::RISK_LOW);
            $table->boolean('follow_up_recommended')->default(false);
            $table->text('follow_up_notes')->nullable();
            $table->date('next_session_recommended_at')->nullable();

            $table->foreignId('created_by')->nullable();
            $table->foreignId('updated_by')->nullable();

            $table->timestamps();

            $table->unique('appointment_id', 'sessions_appointment_unique');
            $table->index(['status', 'clinical_risk_level'], 'sessions_status_risk_idx');
            $table->index(['client_profile_id', 'status'], 'sessions_client_status_idx');
            $table->index(['counsellor_profile_id', 'status'], 'sessions_counsellor_status_idx');
            $table->index('completed_at', 'sessions_completed_idx');

            $table
                ->foreign('appointment_id', 'sessions_appointment_fk')
                ->references('id')
                ->on('appointments')
                ->restrictOnDelete();

            $table
                ->foreign('client_profile_id', 'sessions_client_fk')
                ->references('id')
                ->on('client_profiles')
                ->restrictOnDelete();

            $table
                ->foreign('counsellor_profile_id', 'sessions_counsellor_fk')
                ->references('id')
                ->on('counsellor_profiles')
                ->restrictOnDelete();

            $table
                ->foreign('created_by', 'sessions_created_by_fk')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table
                ->foreign('updated_by', 'sessions_updated_by_fk')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('counselling_sessions');
    }
};

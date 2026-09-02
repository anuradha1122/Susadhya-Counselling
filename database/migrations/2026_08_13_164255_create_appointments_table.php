<?php

use App\Models\Appointment;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique('appt_uuid_unique');

            $table->unsignedBigInteger('client_profile_id');
            $table->unsignedBigInteger('counsellor_profile_id');
            $table->unsignedBigInteger('counselling_service_id')->nullable();

            $table->date('appointment_date');
            $table->time('start_time');
            $table->time('end_time');

            $table->string('timezone', 80)->default('Asia/Colombo');
            $table->string('mode', 30);
            $table->string('status', 40)->default(Appointment::STATUS_PENDING);

            $table->string('meeting_link')->nullable();
            $table->string('location')->nullable();

            $table->text('client_notes')->nullable();
            $table->text('counsellor_notes')->nullable();
            $table->text('admin_notes')->nullable();

            $table->unsignedBigInteger('rescheduled_from_appointment_id')->nullable();

            $table->text('cancellation_reason')->nullable();
            $table->unsignedBigInteger('cancelled_by')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->timestamp('reminder_scheduled_at')->nullable();
            $table->timestamp('reminder_sent_at')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();

            $table
                ->foreign('client_profile_id', 'appt_client_fk')
                ->references('id')
                ->on('client_profiles')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table
                ->foreign('counsellor_profile_id', 'appt_counsellor_fk')
                ->references('id')
                ->on('counsellor_profiles')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table
                ->foreign('counselling_service_id', 'appt_service_fk')
                ->references('id')
                ->on('counselling_services')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table
                ->foreign('rescheduled_from_appointment_id', 'appt_rescheduled_from_fk')
                ->references('id')
                ->on('appointments')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table
                ->foreign('cancelled_by', 'appt_cancelled_by_fk')
                ->references('id')
                ->on('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table
                ->foreign('created_by', 'appt_created_by_fk')
                ->references('id')
                ->on('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table
                ->foreign('updated_by', 'appt_updated_by_fk')
                ->references('id')
                ->on('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->index(
                ['client_profile_id', 'appointment_date', 'start_time'],
                'appt_client_date_time_idx'
            );

            $table->index(
                ['counsellor_profile_id', 'appointment_date', 'start_time'],
                'appt_counsellor_date_time_idx'
            );

            $table->index(
                ['status', 'appointment_date'],
                'appt_status_date_idx'
            );

            $table->index(
                ['mode', 'appointment_date'],
                'appt_mode_date_idx'
            );

            $table->index(
                ['reminder_scheduled_at', 'reminder_sent_at'],
                'appt_reminder_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'session_feedback',
            function (Blueprint $table): void {
                $table->id();

                $table
                    ->uuid('uuid')
                    ->unique('sf_uuid_uk');

                $table->unsignedBigInteger(
                    'appointment_id'
                );

                $table->unsignedBigInteger(
                    'client_profile_id'
                );

                $table->unsignedTinyInteger(
                    'overall_rating'
                );

                $table
                    ->unsignedTinyInteger(
                        'technical_rating'
                    )
                    ->nullable();

                $table
                    ->text('comment')
                    ->nullable();

                $table
                    ->boolean(
                        'would_recommend'
                    )
                    ->nullable();

                $table
                    ->boolean(
                        'consent_to_follow_up'
                    )
                    ->default(false);

                $table->timestamp(
                    'submitted_at'
                );

                $table->timestamps();

                /*
                 * One feedback submission per appointment.
                 */
                $table->unique(
                    'appointment_id',
                    'sf_appointment_uk'
                );

                $table
                    ->foreign(
                        'appointment_id',
                        'sf_appointment_fk'
                    )
                    ->references('id')
                    ->on('appointments')
                    ->restrictOnDelete();

                $table
                    ->foreign(
                        'client_profile_id',
                        'sf_client_fk'
                    )
                    ->references('id')
                    ->on('client_profiles')
                    ->restrictOnDelete();

                $table->index(
                    'client_profile_id',
                    'sf_client_idx'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'session_feedback'
        );
    }
};

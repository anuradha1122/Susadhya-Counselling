<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\ClientProfile;
use App\Models\CounsellingService;
use App\Models\CounsellorProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Appointment>
 */
class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    public function definition(): array
    {
        $appointmentDate = fake()
            ->dateTimeBetween(
                '+1 day',
                '+30 days'
            );

        $startHour =
            fake()->numberBetween(
                8,
                15
            );

        $startTime =
            sprintf(
                '%02d:00:00',
                $startHour
            );

        $endTime =
            sprintf(
                '%02d:00:00',
                $startHour + 1
            );

        return [
            'client_profile_id' => ClientProfile::factory(),

            'counsellor_profile_id' => CounsellorProfile::factory(),

            /*
             * Generic appointments may still be used
             * by tests/modules that do not care about
             * counselling-service or financial data.
             *
             * Use ->withCounsellingService() for
             * booking/rescheduling/payment scenarios.
             */
            'counselling_service_id' => null,

            'fee_amount' => null,

            'fee_currency' => null,

            'appointment_date' => $appointmentDate
                ->format('Y-m-d'),

            'start_time' => $startTime,

            'end_time' => $endTime,

            'timezone' => 'Asia/Colombo',

            'mode' => Appointment::MODE_ONLINE,

            'status' => Appointment::STATUS_PENDING,

            'meeting_link' => null,

            'location' => null,

            'client_notes' => null,

            'counsellor_notes' => null,

            'admin_notes' => null,

            'rescheduled_from_appointment_id' => null,

            'cancellation_reason' => null,

            'cancelled_by' => null,

            'cancelled_at' => null,

            'reminder_scheduled_at' => null,

            'reminder_sent_at' => null,

            'created_by' => null,

            'updated_by' => null,
        ];
    }

    public function withCounsellingService(
        ?CounsellingService $service = null
    ): static {
        return $this->state(
            function () use (
                $service
            ): array {
                $service ??=
                    CounsellingService::factory()
                        ->create([
                            'service_mode' => 'both',

                            'duration_minutes' => 60,

                            'price' => '4500.00',

                            'currency' => 'LKR',

                            'status' => 'active',
                        ]);

                return [
                    'counselling_service_id' => $service->id,

                    'fee_amount' => $service->price,

                    'fee_currency' => strtoupper(
                        $service->currency
                        ?: 'LKR'
                    ),
                ];
            }
        );
    }

    public function confirmed(): static
    {
        return $this->state(
            fn (): array => [
                'status' => Appointment::STATUS_CONFIRMED,
            ]
        );
    }

    public function completed(): static
    {
        return $this->state(
            fn (): array => [
                'status' => Appointment::STATUS_COMPLETED,
            ]
        );
    }

    public function cancelled(): static
    {
        return $this->state(
            fn (): array => [
                'status' => Appointment::STATUS_CANCELLED,

                'cancellation_reason' => 'Cancelled for testing.',

                'cancelled_at' => now(),
            ]
        );
    }

    public function noShow(): static
    {
        return $this->state(
            fn (): array => [
                'status' => Appointment::STATUS_NO_SHOW,
            ]
        );
    }

    public function online(): static
    {
        return $this->state(
            fn (): array => [
                'mode' => Appointment::MODE_ONLINE,

                'meeting_link' => 'https://meet.example.test/session',

                'location' => null,
            ]
        );
    }

    public function inPerson(): static
    {
        return $this->state(
            fn (): array => [
                'mode' => Appointment::MODE_IN_PERSON,

                'meeting_link' => null,

                'location' => 'Susadhya Counselling Centre',
            ]
        );
    }
}

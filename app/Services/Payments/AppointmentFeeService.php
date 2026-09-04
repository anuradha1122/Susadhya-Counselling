<?php

namespace App\Services\Payments;

use App\Models\Appointment;
use Illuminate\Validation\ValidationException;

class AppointmentFeeService
{
    public function snapshot(
        Appointment $appointment
    ): array {
        if (
            $appointment->fee_amount !== null
            && filled(
                $appointment->fee_currency
            )
        ) {
            return [
                'amount' => $appointment->fee_amount,
                'currency' => $appointment->fee_currency,
            ];
        }

        $appointment->loadMissing(
            'counsellingService'
        );

        $service =
            $appointment->counsellingService;

        if (! $service) {
            throw ValidationException::withMessages([
                'appointment' => 'The appointment does not have a counselling service with a valid fee.',
            ]);
        }

        $appointment->forceFill([
            'fee_amount' => $service->price,
            'fee_currency' => strtoupper(
                $service->currency
            ),
        ])->save();

        return [
            'amount' => $appointment->fee_amount,
            'currency' => $appointment->fee_currency,
        ];
    }
}

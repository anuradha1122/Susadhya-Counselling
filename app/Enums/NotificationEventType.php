<?php

namespace App\Enums;

enum NotificationEventType: string
{
    case AppointmentBooked = 'appointment.booked';
    case AppointmentConfirmed = 'appointment.confirmed';
    case AppointmentRescheduled = 'appointment.rescheduled';
    case AppointmentCancelled = 'appointment.cancelled';
    case AppointmentReminder = 'appointment.reminder';

    case PaymentPaid = 'payment.paid';
    case PaymentFailed = 'payment.failed';

    case RefundRequested = 'refund.requested';
    case RefundApproved = 'refund.approved';
    case RefundRejected = 'refund.rejected';
    case RefundCompleted = 'refund.completed';

    public function label(): string
    {
        return match ($this) {
            self::AppointmentBooked => 'Appointment booked',
            self::AppointmentConfirmed => 'Appointment confirmed',
            self::AppointmentRescheduled => 'Appointment rescheduled',
            self::AppointmentCancelled => 'Appointment cancelled',
            self::AppointmentReminder => 'Appointment reminder',
            self::PaymentPaid => 'Payment received',
            self::PaymentFailed => 'Payment failed',
            self::RefundRequested => 'Refund requested',
            self::RefundApproved => 'Refund approved',
            self::RefundRejected => 'Refund rejected',
            self::RefundCompleted => 'Refund completed',
        };
    }

    public function isCritical(): bool
    {
        return match ($this) {
            self::AppointmentBooked,
            self::AppointmentConfirmed,
            self::AppointmentRescheduled,
            self::AppointmentCancelled,
            self::PaymentPaid,
            self::PaymentFailed,
            self::RefundApproved,
            self::RefundRejected,
            self::RefundCompleted => true,

            self::AppointmentReminder,
            self::RefundRequested => false,
        };
    }
}

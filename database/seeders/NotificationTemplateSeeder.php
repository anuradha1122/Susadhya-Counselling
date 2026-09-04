<?php

namespace Database\Seeders;

use App\Models\NotificationTemplate;
use Illuminate\Database\Seeder;

class NotificationTemplateSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->templates() as $template) {
            NotificationTemplate::query()
                ->firstOrCreate(
                    [
                        'key' => $template['key'],
                    ],
                    $template
                );
        }
    }

    private function templates(): array
    {
        return [
            [
                'key' => 'appointment.booked.client',
                'name' => 'Appointment booked - client',
                'subject' => 'Appointment request received',
                'in_app_body' => 'Your {{ service_name }} appointment request for {{ starts_at }} has been received.',
                'email_body' => 'Your {{ service_name }} appointment request for {{ starts_at }} has been received. You can review the current booking status after signing in.',
                'sms_body' => 'Susadhya: Your appointment request for {{ starts_at }} has been received.',
                'variables' => [
                    'service_name',
                    'starts_at',
                    'mode',
                ],
                'is_active' => true,
            ],

            [
                'key' => 'appointment.booked.counsellor',
                'name' => 'Appointment booked - counsellor',
                'subject' => 'New appointment request',
                'in_app_body' => 'A new {{ service_name }} appointment has been requested for {{ starts_at }}.',
                'email_body' => 'A new {{ service_name }} appointment has been requested for {{ starts_at }}. Sign in to review the booking.',
                'sms_body' => 'Susadhya: New appointment request for {{ starts_at }}.',
                'variables' => [
                    'service_name',
                    'starts_at',
                    'mode',
                ],
                'is_active' => true,
            ],

            [
                'key' => 'appointment.confirmed.client',
                'name' => 'Appointment confirmed - client',
                'subject' => 'Appointment confirmed',
                'in_app_body' => 'Your {{ service_name }} appointment for {{ starts_at }} has been confirmed.',
                'email_body' => 'Your {{ service_name }} appointment for {{ starts_at }} has been confirmed.',
                'sms_body' => 'Susadhya: Your appointment for {{ starts_at }} is confirmed.',
                'variables' => [
                    'service_name',
                    'starts_at',
                    'mode',
                ],
                'is_active' => true,
            ],

            [
                'key' => 'appointment.rescheduled.client',
                'name' => 'Appointment rescheduled - client',
                'subject' => 'Appointment rescheduled',
                'in_app_body' => 'Your appointment has been rescheduled to {{ starts_at }}.',
                'email_body' => 'Your {{ service_name }} appointment has been rescheduled to {{ starts_at }}.',
                'sms_body' => 'Susadhya: Your appointment is now scheduled for {{ starts_at }}.',
                'variables' => [
                    'service_name',
                    'starts_at',
                ],
                'is_active' => true,
            ],

            [
                'key' => 'appointment.rescheduled.counsellor',
                'name' => 'Appointment rescheduled - counsellor',
                'subject' => 'Appointment rescheduled',
                'in_app_body' => 'An appointment has been rescheduled to {{ starts_at }}.',
                'email_body' => 'A {{ service_name }} appointment has been rescheduled to {{ starts_at }}.',
                'sms_body' => 'Susadhya: Appointment rescheduled to {{ starts_at }}.',
                'variables' => [
                    'service_name',
                    'starts_at',
                ],
                'is_active' => true,
            ],

            [
                'key' => 'appointment.cancelled.client',
                'name' => 'Appointment cancelled - client',
                'subject' => 'Appointment cancelled',
                'in_app_body' => 'Your {{ service_name }} appointment scheduled for {{ starts_at }} has been cancelled.',
                'email_body' => 'Your {{ service_name }} appointment scheduled for {{ starts_at }} has been cancelled.',
                'sms_body' => 'Susadhya: Your appointment for {{ starts_at }} has been cancelled.',
                'variables' => [
                    'service_name',
                    'starts_at',
                ],
                'is_active' => true,
            ],

            [
                'key' => 'appointment.cancelled.counsellor',
                'name' => 'Appointment cancelled - counsellor',
                'subject' => 'Appointment cancelled',
                'in_app_body' => 'An appointment scheduled for {{ starts_at }} has been cancelled.',
                'email_body' => 'A {{ service_name }} appointment scheduled for {{ starts_at }} has been cancelled.',
                'sms_body' => 'Susadhya: Appointment for {{ starts_at }} has been cancelled.',
                'variables' => [
                    'service_name',
                    'starts_at',
                ],
                'is_active' => true,
            ],

            [
                'key' => 'appointment.reminder.client',
                'name' => 'Appointment reminder - client',
                'subject' => 'Appointment reminder',
                'in_app_body' => 'Reminder: your {{ service_name }} appointment is scheduled for {{ starts_at }}.',
                'email_body' => 'This is a reminder that your {{ service_name }} appointment is scheduled for {{ starts_at }}.',
                'sms_body' => 'Susadhya reminder: Appointment at {{ starts_at }}.',
                'variables' => [
                    'service_name',
                    'starts_at',
                ],
                'is_active' => true,
            ],

            [
                'key' => 'appointment.reminder.counsellor',
                'name' => 'Appointment reminder - counsellor',
                'subject' => 'Appointment reminder',
                'in_app_body' => 'Reminder: you have a {{ service_name }} appointment scheduled for {{ starts_at }}.',
                'email_body' => 'This is a reminder that you have a {{ service_name }} appointment scheduled for {{ starts_at }}.',
                'sms_body' => 'Susadhya reminder: Appointment at {{ starts_at }}.',
                'variables' => [
                    'service_name',
                    'starts_at',
                ],
                'is_active' => true,
            ],

            [
                'key' => 'payment.paid.client',
                'name' => 'Payment received - client',
                'subject' => 'Payment received',
                'in_app_body' => 'Your payment of {{ currency }} {{ amount }} has been received.',
                'email_body' => 'Your payment of {{ currency }} {{ amount }} has been received successfully. Your receipt is available from your Susadhya payment history.',
                'sms_body' => 'Susadhya: Payment of {{ currency }} {{ amount }} received.',
                'variables' => [
                    'amount',
                    'currency',
                    'reference',
                ],
                'is_active' => true,
            ],

            [
                'key' => 'payment.failed.client',
                'name' => 'Payment failed - client',
                'subject' => 'Payment was not completed',
                'in_app_body' => 'Your payment of {{ currency }} {{ amount }} was not completed.',
                'email_body' => 'Your payment of {{ currency }} {{ amount }} was not completed. Sign in to review the payment status before trying again.',
                'sms_body' => 'Susadhya: Payment of {{ currency }} {{ amount }} was not completed.',
                'variables' => [
                    'amount',
                    'currency',
                ],
                'is_active' => true,
            ],

            [
                'key' => 'refund.requested.finance',
                'name' => 'Refund requested - finance',
                'subject' => 'New refund request',
                'in_app_body' => 'A refund request for {{ currency }} {{ amount }} requires finance review.',
                'email_body' => 'A refund request for {{ currency }} {{ amount }} requires review. Sign in to the finance portal to process it.',
                'sms_body' => 'Susadhya: Refund request requires finance review.',
                'variables' => [
                    'amount',
                    'currency',
                ],
                'is_active' => true,
            ],

            [
                'key' => 'refund.approved.client',
                'name' => 'Refund approved - client',
                'subject' => 'Refund approved',
                'in_app_body' => 'Your refund request for {{ currency }} {{ amount }} has been approved.',
                'email_body' => 'Your refund request for {{ currency }} {{ amount }} has been approved. Processing status is available in your payment history.',
                'sms_body' => 'Susadhya: Refund of {{ currency }} {{ amount }} approved.',
                'variables' => [
                    'amount',
                    'currency',
                ],
                'is_active' => true,
            ],

            [
                'key' => 'refund.rejected.client',
                'name' => 'Refund rejected - client',
                'subject' => 'Refund request decision',
                'in_app_body' => 'Your refund request for {{ currency }} {{ amount }} was not approved.',
                'email_body' => 'Your refund request for {{ currency }} {{ amount }} was not approved. Sign in to view the current payment/refund status.',
                'sms_body' => 'Susadhya: Your refund request was not approved.',
                'variables' => [
                    'amount',
                    'currency',
                ],
                'is_active' => true,
            ],

            [
                'key' => 'refund.refunded.client',
                'name' => 'Refund completed - client',
                'subject' => 'Refund completed',
                'in_app_body' => 'Your refund of {{ currency }} {{ amount }} has been processed.',
                'email_body' => 'Your refund of {{ currency }} {{ amount }} has been processed.',
                'sms_body' => 'Susadhya: Refund of {{ currency }} {{ amount }} processed.',
                'variables' => [
                    'amount',
                    'currency',
                ],
                'is_active' => true,
            ],
        ];
    }
}

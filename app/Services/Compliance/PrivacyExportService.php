<?php

namespace App\Services\Compliance;

use App\Models\PrivacyRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PrivacyExportService
{
    public function __construct(
        private readonly PrivacyRequestService $privacyRequestService,
        private readonly AuditLogger $auditLogger,
    ) {}

    public function prepare(
        PrivacyRequest $privacyRequest,
        User $actor
    ): PrivacyRequest {
        if (
            $privacyRequest->status
            !== PrivacyRequest::STATUS_APPROVED
        ) {
            throw ValidationException::withMessages([
                'status' => 'The privacy request must be approved before an export can be prepared.',
            ]);
        }

        if (
            ! in_array(
                $privacyRequest->type,
                [
                    PrivacyRequest::TYPE_ACCESS,
                    PrivacyRequest::TYPE_EXPORT,
                ],
                true
            )
        ) {
            throw ValidationException::withMessages([
                'type' => 'Only access or export requests can generate an automatic privacy export.',
            ]);
        }

        $subject =
            User::query()->findOrFail(
                $privacyRequest->subject_user_id
            );

        $payload =
            $this->buildPayload(
                $privacyRequest,
                $subject
            );

        $json =
            json_encode(
                $payload,
                JSON_PRETTY_PRINT
                | JSON_UNESCAPED_SLASHES
                | JSON_UNESCAPED_UNICODE
                | JSON_THROW_ON_ERROR
            );

        $checksum =
            hash(
                'sha256',
                $json
            );

        $disk =
            (string) config(
                'compliance.privacy_export_disk',
                'local'
            );

        $directory =
            trim(
                (string) config(
                    'compliance.privacy_export_directory',
                    'privacy-exports'
                ),
                '/'
            );

        $filename =
            sprintf(
                '%s/%s-%s.json',
                $directory,
                $privacyRequest->uuid,
                Str::lower(
                    Str::random(16)
                )
            );

        Storage::disk($disk)
            ->put(
                $filename,
                $json
            );

        return $this
            ->privacyRequestService
            ->markExportReady(
                privacyRequest: $privacyRequest,
                actor: $actor,
                disk: $disk,
                path: $filename,
                checksum: $checksum,
            );
    }

    public function purgeExpiredExports(): int
    {
        $ttlDays =
            max(
                1,
                (int) config(
                    'compliance.privacy_export_ttl_days',
                    14
                )
            );

        $cutoff =
            now()->subDays(
                $ttlDays
            );

        $requests =
            PrivacyRequest::query()
                ->whereNotNull(
                    'export_path'
                )
                ->whereNotNull(
                    'export_disk'
                )
                ->whereNotNull(
                    'export_prepared_at'
                )
                ->where(
                    'export_prepared_at',
                    '<',
                    $cutoff
                )
                ->get();

        $purged = 0;

        foreach (
            $requests as $request
        ) {
            $disk =
                $request->export_disk;

            $path =
                $request->export_path;

            if (
                is_string($disk)
                && is_string($path)
                && Storage::disk(
                    $disk
                )->exists($path)
            ) {
                Storage::disk(
                    $disk
                )->delete($path);
            }

            $request->forceFill([
                'export_path' => null,

                'export_disk' => null,
            ])->save();

            $this->auditLogger->record(
                category: 'privacy',
                event: 'privacy_request.export_expired',
                action: 'purge_export',
                subject: $request,
                metadata: [
                    'export_prepared_at' => optional(
                        $request
                            ->export_prepared_at
                    )?->toIso8601String(),
                ],
            );

            $purged++;
        }

        return $purged;
    }

    private function buildPayload(
        PrivacyRequest $request,
        User $subject
    ): array {
        $clientProfile =
            $this->clientProfile(
                $subject
            );

        $appointments =
            $this->appointments(
                $clientProfile
            );

        return [
            'export' => [
                'privacy_request_uuid' => $request->uuid,

                'generated_at' => now()
                    ->toIso8601String(),

                'format_version' => '1.0',

                'notice' => 'This automated export contains reviewed non-clinical account and service metadata. Highly sensitive clinical records, session narratives, screening answers, intake answers, diagnoses, secure document contents and payment-provider secrets require separate review before disclosure.',
            ],

            'account' => $this->account(
                $subject
            ),

            'client_profile' => $clientProfile !== null
                    ? $this->safeRow(
                        $clientProfile,
                        [
                            'uuid',
                            'first_name',
                            'middle_name',
                            'last_name',
                            'display_name',
                            'date_of_birth',
                            'gender',
                            'phone',
                            'mobile',
                            'address',
                            'city',
                            'district',
                            'preferred_language',
                            'preferred_contact_method',
                            'status',
                            'profile_completion_percentage',
                            'created_at',
                            'updated_at',
                        ]
                    )
                    : null,

            'consent_history' => $this->consentHistory(
                $clientProfile
            ),

            'appointments' => array_map(
                fn (object $appointment): array => $this->safeRow(
                    $appointment,
                    [
                        'uuid',
                        'appointment_date',
                        'start_time',
                        'end_time',
                        'timezone',
                        'mode',
                        'status',
                        'fee_amount',
                        'fee_currency',
                        'created_at',
                        'updated_at',
                    ]
                ),
                $appointments
            ),

            'payments' => $this->payments(
                $appointments
            ),

            'manual_review_required_for' => [
                'clinical notes and clinical note versions',
                'session notes and counselling narratives',
                'intake answers',
                'screening answers',
                'diagnostic or risk information',
                'secure document contents',
                'records subject to third-party confidentiality',
                'records subject to legal or professional retention restrictions',
            ],
        ];
    }

    private function account(
        User $user
    ): array {
        if (
            ! Schema::hasTable(
                'users'
            )
        ) {
            return [];
        }

        $row =
            DB::table('users')
                ->where(
                    'id',
                    $user->id
                )
                ->first();

        if ($row === null) {
            return [];
        }

        return $this->safeRow(
            $row,
            [
                'uuid',
                'name',
                'email',
                'email_verified_at',
                'is_active',
                'status',
                'created_at',
                'updated_at',
            ]
        );
    }

    private function clientProfile(
        User $user
    ): ?object {
        if (
            ! Schema::hasTable(
                'client_profiles'
            )
            || ! Schema::hasColumn(
                'client_profiles',
                'user_id'
            )
        ) {
            return null;
        }

        return DB::table(
            'client_profiles'
        )
            ->where(
                'user_id',
                $user->id
            )
            ->first();
    }

    private function consentHistory(
        ?object $clientProfile
    ): array {
        if (
            $clientProfile === null
            || ! isset(
                $clientProfile->id
            )
            || ! Schema::hasTable(
                'client_consents'
            )
        ) {
            return [];
        }

        $foreignKey =
            $this->firstExistingColumn(
                'client_consents',
                [
                    'client_profile_id',
                    'client_id',
                ]
            );

        if ($foreignKey === null) {
            return [];
        }

        $rows =
            DB::table(
                'client_consents'
            )
                ->where(
                    $foreignKey,
                    $clientProfile->id
                )
                ->orderBy(
                    'created_at'
                )
                ->get();

        return $rows
            ->map(
                fn (object $row): array => $this->safeRow(
                    $row,
                    [
                        'uuid',
                        'consent_type',
                        'consent_key',
                        'consent_version',
                        'version',
                        'granted',
                        'is_granted',
                        'consented_at',
                        'granted_at',
                        'withdrawn_at',
                        'source',
                        'created_at',
                        'updated_at',
                    ]
                )
            )
            ->values()
            ->all();
    }

    private function appointments(
        ?object $clientProfile
    ): array {
        if (
            $clientProfile === null
            || ! isset(
                $clientProfile->id
            )
            || ! Schema::hasTable(
                'appointments'
            )
            || ! Schema::hasColumn(
                'appointments',
                'client_profile_id'
            )
        ) {
            return [];
        }

        return DB::table(
            'appointments'
        )
            ->where(
                'client_profile_id',
                $clientProfile->id
            )
            ->orderBy(
                'appointment_date'
            )
            ->get()
            ->all();
    }

    private function payments(
        array $appointments
    ): array {
        if (
            $appointments === []
            || ! Schema::hasTable(
                'payments'
            )
            || ! Schema::hasColumn(
                'payments',
                'appointment_id'
            )
        ) {
            return [];
        }

        $appointmentIds =
            collect(
                $appointments
            )
                ->pluck('id')
                ->filter()
                ->values()
                ->all();

        if ($appointmentIds === []) {
            return [];
        }

        $rows =
            DB::table('payments')
                ->whereIn(
                    'appointment_id',
                    $appointmentIds
                )
                ->orderBy(
                    'created_at'
                )
                ->get();

        return $rows
            ->map(
                fn (object $row): array => $this->safeRow(
                    $row,
                    [
                        'uuid',
                        'payment_number',
                        'payment_reference',
                        'reference',
                        'method',
                        'status',
                        'amount',
                        'currency',
                        'reconciliation_status',
                        'paid_at',
                        'created_at',
                        'updated_at',
                    ]
                )
            )
            ->values()
            ->all();
    }

    private function safeRow(
        object|array $row,
        array $allowedFields
    ): array {
        $data =
            (array) $row;

        $safe = [];

        foreach (
            $allowedFields as $field
        ) {
            if (
                array_key_exists(
                    $field,
                    $data
                )
            ) {
                $safe[$field] =
                    $data[$field];
            }
        }

        return $safe;
    }

    private function firstExistingColumn(
        string $table,
        array $columns
    ): ?string {
        foreach (
            $columns as $column
        ) {
            if (
                Schema::hasColumn(
                    $table,
                    $column
                )
            ) {
                return $column;
            }
        }

        return null;
    }
}

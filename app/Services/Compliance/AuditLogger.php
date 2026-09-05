<?php

namespace App\Services\Compliance;

use App\Models\AuditEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class AuditLogger
{
    private const SENSITIVE_KEYWORDS = [
        'password',
        'password_confirmation',
        'token',
        'secret',
        'api_key',
        'authorization',
        'cookie',
        'clinical_note',
        'clinical_notes',
        'session_note',
        'session_notes',
        'narrative',
        'diagnosis',
        'diagnoses',
        'presenting_concern',
        'presenting_concerns',
        'screening_answer',
        'screening_answers',
        'intake_answer',
        'intake_answers',
        'card_number',
        'cvv',
        'cvc',
        'provider_payload',
        'gateway_payload',
        'raw_payload',
    ];

    public function record(
        string $category,
        string $event,
        string $action,
        ?Model $subject = null,
        string $result = 'success',
        ?string $purposeCode = null,
        ?string $reason = null,
        array $metadata = [],
        ?User $actor = null,
        ?Request $request = null,
    ): AuditEvent {
        $request ??=
            $this->currentRequest();

        if (
            $actor === null
            && $request?->user() instanceof User
        ) {
            $actor = $request->user();
        }

        $subjectUuid = null;

        if (
            $subject !== null
            && isset($subject->uuid)
            && is_string($subject->uuid)
        ) {
            $subjectUuid =
                $subject->uuid;
        }

        return AuditEvent::query()->create([
            'actor_id' => $actor?->id,

            'category' => $category,

            'event' => Str::limit(
                $event,
                160,
                ''
            ),

            'action' => Str::limit(
                $action,
                160,
                ''
            ),

            'subject_type' => $subject?->getMorphClass(),

            'subject_id' => $subject !== null
                    ? (string) $subject->getKey()
                    : null,

            'subject_uuid' => $subjectUuid,

            'result' => Str::limit(
                $result,
                32,
                ''
            ),

            'purpose_code' => $purposeCode !== null
                    ? Str::limit(
                        $purposeCode,
                        100,
                        ''
                    )
                    : null,

            'reason' => $reason,

            'metadata' => $this->sanitizeMetadata(
                $metadata
            ),

            'ip_address' => $request?->ip(),

            'user_agent' => $request?->userAgent(),

            'request_id' => $this->resolveRequestId(
                $request
            ),

            'occurred_at' => now(),
        ]);
    }

    private function currentRequest(): ?Request
    {
        if (
            ! app()->bound('request')
        ) {
            return null;
        }

        $request = request();

        return $request instanceof Request
            ? $request
            : null;
    }

    private function resolveRequestId(
        ?Request $request
    ): ?string {
        if ($request === null) {
            return null;
        }

        $requestId =
            $request->headers->get(
                'X-Request-ID'
            );

        if (
            is_string($requestId)
            && $requestId !== ''
        ) {
            return Str::limit(
                $requestId,
                80,
                ''
            );
        }

        return null;
    }

    private function sanitizeMetadata(
        array $metadata
    ): array {
        $clean = [];

        foreach (
            $metadata as $key => $value
        ) {
            $normalizedKey =
                Str::lower(
                    (string) $key
                );

            if (
                $this->isSensitiveKey(
                    $normalizedKey
                )
            ) {
                continue;
            }

            if (is_array($value)) {
                $clean[$key] =
                    $this->sanitizeMetadata(
                        $value
                    );

                continue;
            }

            if (
                is_object($value)
                && ! $value instanceof \Stringable
            ) {
                continue;
            }

            if (is_string($value)) {
                $clean[$key] =
                    Str::limit(
                        $value,
                        1000,
                        '…'
                    );

                continue;
            }

            if (
                is_null($value)
                || is_bool($value)
                || is_int($value)
                || is_float($value)
            ) {
                $clean[$key] = $value;
            }
        }

        return Arr::where(
            $clean,
            fn ($value) => $value !== null
        );
    }

    private function isSensitiveKey(
        string $key
    ): bool {
        foreach (
            self::SENSITIVE_KEYWORDS as $keyword
        ) {
            if (
                Str::contains(
                    $key,
                    $keyword
                )
            ) {
                return true;
            }
        }

        return false;
    }
}

<?php

namespace App\Services\Clinical;

use App\Models\ClientCase;
use App\Models\ClinicalRecordAccessLog;
use App\Models\User;
use Illuminate\Http\Request;

class ClinicalRecordAccessLogger
{
    public function log(
        User $actor,
        ClientCase $case,
        string $recordType,
        ?int $recordId,
        string $action,
        Request $request,
        array $metadata = [],
    ): ClinicalRecordAccessLog {
        return ClinicalRecordAccessLog::create([
            'actor_id' => $actor->id,
            'client_case_id' => $case->id,
            'record_type' => $recordType,
            'record_id' => $recordId,
            'action' => $action,
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr(
                (string) $request->userAgent(),
                0,
                1000,
            ),
            'metadata' => $metadata ?: null,
        ]);
    }
}

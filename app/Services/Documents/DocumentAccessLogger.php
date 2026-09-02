<?php

namespace App\Services\Documents;

use App\Models\DocumentAccessLog;
use App\Models\SecureDocument;
use App\Models\User;
use Illuminate\Http\Request;

class DocumentAccessLogger
{
    public function log(
        ?User $actor,
        SecureDocument $document,
        string $action,
        Request $request,
        array $metadata = [],
    ): DocumentAccessLog {
        return DocumentAccessLog::query()
            ->create([
                'actor_id' => $actor?->id,

                'secure_document_id' => $document->id,

                'document_uuid' => $document->uuid,

                'action' => $action,

                'ip_address' => $request->ip(),

                'user_agent' => $request->userAgent(),

                'metadata' => $metadata ?: null,
            ]);
    }
}

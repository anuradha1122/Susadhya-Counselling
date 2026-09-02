<?php

namespace App\Services\Clinical;

use App\Models\ClinicalNote;
use App\Models\ClinicalNoteVersion;
use App\Models\User;

class ClinicalNoteVersionService
{
    public function snapshot(
        ClinicalNote $note,
        User $actor,
        ?string $reason = null,
    ): ClinicalNoteVersion {
        return ClinicalNoteVersion::create([
            'clinical_note_id' => $note->id,
            'version' => $note->version,
            'title' => $note->title,
            'note' => $note->note,
            'formulation' => $note->formulation,
            'intervention' => $note->intervention,
            'risk_assessment' => $note->risk_assessment,
            'plan' => $note->plan,
            'risk_level' => $note->risk_level,
            'status' => $note->status,
            'changed_by' => $actor->id,
            'change_reason' => $reason,
            'signed_at' => $note->signed_at,
            'locked_at' => $note->locked_at,
        ]);
    }
}

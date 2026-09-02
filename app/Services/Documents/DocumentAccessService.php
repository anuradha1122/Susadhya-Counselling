<?php

namespace App\Services\Documents;

use App\Models\SecureDocument;
use App\Models\User;

class DocumentAccessService
{
    public function canAccess(
        User $user,
        SecureDocument $document,
    ): bool {
        $document->loadMissing([
            'clientProfile.user',
            'clientCase.counsellorProfile.user',
        ]);

        if (
            $user->hasRole(
                'super_admin'
            )
        ) {
            return true;
        }

        if (
            $document->uploaded_by
            === $user->id
        ) {
            return true;
        }

        $clientOwner =
            $document
                ->clientProfile
                ?->user_id
            === $user->id;

        $assignedCounsellor =
            $document
                ->clientCase
                ?->counsellorProfile
                ?->user_id
            === $user->id;

        $clinicalSupervisor =
            $user->can(
                'documents.case.review'
            );

        $administrator =
            $user->can(
                'documents.admin.manage'
            );

        return match (
            $document->access_scope
        ) {
            SecureDocument::SCOPE_CLIENT => $clientOwner,

            SecureDocument::SCOPE_CARE_TEAM => $clientOwner
                || $assignedCounsellor
                || $clinicalSupervisor,

            SecureDocument::SCOPE_CLINICAL => $assignedCounsellor
                || $clinicalSupervisor,

            SecureDocument::SCOPE_SUPERVISOR => $clinicalSupervisor,

            SecureDocument::SCOPE_ADMIN => $administrator,

            SecureDocument::SCOPE_SHARED => $clientOwner
                || $assignedCounsellor
                || $clinicalSupervisor
                || $administrator,

            default => false,
        };
    }

    public function canDelete(
        User $user,
        SecureDocument $document,
    ): bool {
        if (
            $user->hasRole(
                'super_admin'
            )
        ) {
            return true;
        }

        if (
            $document->uploaded_by
                === $user->id
            && $this->canAccess(
                $user,
                $document
            )
        ) {
            return true;
        }

        return $user->can(
            'documents.admin.manage'
        )
            && in_array(
                $document->access_scope,
                [
                    SecureDocument::SCOPE_ADMIN,
                    SecureDocument::SCOPE_SHARED,
                ],
                true
            );
    }

    public function authorizeDownload(
        User $user,
        SecureDocument $document,
    ): void {
        abort_unless(
            $this->canAccess(
                $user,
                $document
            ),
            403
        );

        abort_if(
            $document->isDownloadBlocked(),
            423,
            'This document is not available for download.'
        );
    }
}

<?php

namespace App\Http\Controllers\Compliance;

use App\Http\Controllers\Controller;
use App\Models\AuditEvent;
use App\Models\DataBreach;
use App\Models\PrivacyRequest;
use App\Models\RetentionPolicy;
use Inertia\Inertia;
use Inertia\Response;

class ComplianceDashboardController extends Controller
{
    public function __invoke(): Response
    {
        $pendingPrivacyStatuses = [
            PrivacyRequest::STATUS_SUBMITTED,
            PrivacyRequest::STATUS_IDENTITY_VERIFIED,
            PrivacyRequest::STATUS_UNDER_REVIEW,
            PrivacyRequest::STATUS_APPROVED,
            PrivacyRequest::STATUS_EXPORT_READY,
        ];

        $recentAuditEvents = AuditEvent::query()
            ->with('actor:id,name')
            ->latest('occurred_at')
            ->limit(8)
            ->get()
            ->map(fn (AuditEvent $event): array => [
                'uuid' => $event->uuid,
                'category' => $event->category,
                'event' => $event->event,
                'action' => $event->action,
                'result' => $event->result,
                'actor' => $event->actor?->name,
                'occurred_at' => $event->occurred_at?->toIso8601String(),
            ])
            ->values();

        return Inertia::render(
            'Compliance/Dashboard',
            [
                'stats' => [
                    'auditEvents' => AuditEvent::query()->count(),

                    'pendingPrivacyRequests' => PrivacyRequest::query()
                        ->whereIn(
                            'status',
                            $pendingPrivacyStatuses
                        )
                        ->count(),

                    'openBreaches' => DataBreach::query()
                        ->where(
                            'status',
                            '!=',
                            DataBreach::STATUS_CLOSED
                        )
                        ->count(),

                    'enabledRetentionPolicies' => RetentionPolicy::query()
                        ->where(
                            'enabled',
                            true
                        )
                        ->count(),
                ],

                'recentAuditEvents' => $recentAuditEvents,
            ]
        );
    }
}

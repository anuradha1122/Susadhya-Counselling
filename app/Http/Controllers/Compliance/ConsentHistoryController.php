<?php

namespace App\Http\Controllers\Compliance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class ConsentHistoryController extends Controller
{
    public function index(
        Request $request
    ): Response {
        $filters = $request->validate([
            'search' => [
                'nullable',
                'string',
                'max:150',
            ],

            'type' => [
                'nullable',
                'string',
                'max:100',
            ],
        ]);

        if (
            ! Schema::hasTable(
                'client_consents'
            )
            || ! Schema::hasTable(
                'client_profiles'
            )
        ) {
            return Inertia::render(
                'Compliance/Consents/Index',
                [
                    'available' => false,
                    'consents' => [
                        'data' => [],
                        'links' => [],
                    ],
                    'types' => [],
                    'filters' => $filters,
                ]
            );
        }

        $clientForeignKey =
            $this->firstExistingColumn(
                'client_consents',
                [
                    'client_profile_id',
                    'client_id',
                ]
            );

        if ($clientForeignKey === null) {
            return Inertia::render(
                'Compliance/Consents/Index',
                [
                    'available' => false,
                    'consents' => [
                        'data' => [],
                        'links' => [],
                    ],
                    'types' => [],
                    'filters' => $filters,
                ]
            );
        }

        $consentColumns =
            Schema::getColumnListing(
                'client_consents'
            );

        $select = [
            'cc.id',
            'u.name as client_name',
            'u.email as client_email',
        ];

        if (
            Schema::hasColumn(
                'users',
                'uuid'
            )
        ) {
            $select[] =
                'u.uuid as client_uuid';
        }

        $safeColumns = [
            'uuid',
            'consent_type',
            'consent_key',
            'consent_version',
            'version',
            'granted',
            'is_granted',
            'accepted',
            'consented_at',
            'granted_at',
            'withdrawn_at',
            'source',
            'created_at',
            'updated_at',
        ];

        foreach (
            $safeColumns as $column
        ) {
            if (
                in_array(
                    $column,
                    $consentColumns,
                    true
                )
            ) {
                $select[] =
                    "cc.{$column}";
            }
        }

        $query =
            DB::table(
                'client_consents as cc'
            )
                ->join(
                    'client_profiles as cp',
                    "cc.{$clientForeignKey}",
                    '=',
                    'cp.id'
                )
                ->join(
                    'users as u',
                    'cp.user_id',
                    '=',
                    'u.id'
                )
                ->select($select);

        if (
            isset($filters['search'])
            && $filters['search'] !== ''
        ) {
            $search = $filters['search'];

            $query->where(
                function ($query) use ($search): void {
                    $query
                        ->where(
                            'u.name',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'u.email',
                            'like',
                            "%{$search}%"
                        );
                }
            );
        }

        $typeColumn =
            $this->firstExistingColumn(
                'client_consents',
                [
                    'consent_type',
                    'consent_key',
                ]
            );

        if (
            $typeColumn !== null
            && isset($filters['type'])
            && $filters['type'] !== ''
        ) {
            $query->where(
                "cc.{$typeColumn}",
                $filters['type']
            );
        }

        $orderColumn =
            in_array(
                'created_at',
                $consentColumns,
                true
            )
                ? 'created_at'
                : 'id';

        $consents =
            $query
                ->orderByDesc(
                    "cc.{$orderColumn}"
                )
                ->paginate(25)
                ->withQueryString();

        $types = [];

        if ($typeColumn !== null) {
            $types =
                DB::table(
                    'client_consents'
                )
                    ->whereNotNull(
                        $typeColumn
                    )
                    ->select(
                        $typeColumn
                    )
                    ->distinct()
                    ->orderBy(
                        $typeColumn
                    )
                    ->pluck(
                        $typeColumn
                    )
                    ->values()
                    ->all();
        }

        return Inertia::render(
            'Compliance/Consents/Index',
            [
                'available' => true,

                'consents' => $consents,

                'types' => $types,

                'filters' => [
                    'search' => $filters['search']
                        ?? '',

                    'type' => $filters['type']
                        ?? '',
                ],
            ]
        );
    }

    private function firstExistingColumn(
        string $table,
        array $columns
    ): ?string {
        foreach ($columns as $column) {
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

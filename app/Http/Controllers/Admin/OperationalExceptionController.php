<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Operations\StoreOperationalExceptionRequest;
use App\Http\Requests\Admin\Operations\UpdateOperationalExceptionRequest;
use App\Models\OperationalException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class OperationalExceptionController extends Controller
{
    public function store(
        StoreOperationalExceptionRequest $request
    ): RedirectResponse {
        $validated = $request->validated();

        OperationalException::query()->create([
            ...$validated,
            'status' => OperationalException::STATUS_OPEN,
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);

        return back()->with(
            'success',
            'Operational exception created.'
        );
    }

    public function update(
        UpdateOperationalExceptionRequest $request,
        OperationalException $operationalException
    ): RedirectResponse {
        $validated = $request->validated();

        DB::transaction(
            function () use (
                $request,
                $validated,
                $operationalException
            ): void {
                $resolved = in_array(
                    $validated['status'],
                    [
                        OperationalException::STATUS_RESOLVED,
                        OperationalException::STATUS_DISMISSED,
                    ],
                    true
                );

                $operationalException->forceFill([
                    ...$validated,
                    'resolved_by' => $resolved
                        ? $request->user()->id
                        : null,
                    'resolved_at' => $resolved
                        ? now()
                        : null,
                    'updated_by' => $request->user()->id,
                ])->save();
            }
        );

        return back()->with(
            'success',
            'Operational exception updated.'
        );
    }
}

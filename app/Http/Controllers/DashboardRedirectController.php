<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardRedirectController extends Controller
{
    public function __invoke(
        Request $request
    ): RedirectResponse {
        $user = $request->user();

        if ($user->can('dashboard.admin.view')) {
            return redirect()
                ->route('admin.dashboard');
        }

        if ($user->hasRole('privacy_officer')) {
            return redirect()
                ->route('compliance.dashboard');
        }

        if ($user->can('dashboard.counsellor.view')) {
            return redirect()
                ->route('counsellor.dashboard');
        }

        if ($user->can('dashboard.client.view')) {
            return redirect()
                ->route('client.dashboard');
        }

        if ($user->hasRole('finance_admin')) {
            return redirect()
                ->route('finance.payments.index');
        }

        abort(
            403,
            'No dashboard has been assigned to this account.'
        );
    }
}

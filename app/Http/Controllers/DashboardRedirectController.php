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

        if ($user->can('dashboard.counsellor.view')) {
            return redirect()
                ->route('counsellor.dashboard');
        }

        abort(
            403,
            'No dashboard has been assigned to this account.'
        );
    }
}

<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ClientSubscription;
use App\Models\ServicePackage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PackageSubscriptionController extends Controller
{
    public function index(Request $request): Response
    {
        $clientProfile = $request->user()->clientProfile;

        return Inertia::render('Client/AdvancedProduct/Packages', [
            'packages' => ServicePackage::query()
                ->with('items.service')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
            'subscriptions' => $clientProfile
                ? ClientSubscription::query()
                    ->with('package:id,uuid,name')
                    ->where('client_profile_id', $clientProfile->id)
                    ->latest()
                    ->get()
                : [],
        ]);
    }

    public function subscribe(Request $request, ServicePackage $package): RedirectResponse
    {
        $clientProfile = $request->user()->clientProfile;

        abort_unless($clientProfile, 403);
        abort_unless($package->is_active, 404);

        ClientSubscription::create([
            'client_profile_id' => $clientProfile->id,
            'service_package_id' => $package->id,
            'status' => 'active',
            'starts_on' => now()->toDateString(),
            'expires_on' => now()->addDays($package->validity_days)->toDateString(),
            'total_sessions' => $package->sessions_count,
            'used_sessions' => 0,
            'amount_paid' => 0,
            'notes' => 'Created from client package request. Confirm payment/admin approval before service consumption.',
        ]);

        return back()->with('success', 'Package request submitted.');
    }
}

<?php

namespace App\Http\Controllers\Admin\AdvancedProduct;

use App\Http\Controllers\Controller;
use App\Models\ClientSubscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ClientSubscriptionController extends Controller
{
    public function index(Request $request): Response
    {
        $subscriptions = ClientSubscription::query()
            ->with(['clientProfile.user:id,name,email', 'package:id,name'])
            ->when($request->string('status')->toString(), function ($query, string $status): void {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/AdvancedProduct/Subscriptions/Index', [
            'subscriptions' => $subscriptions,
            'filters' => $request->only('status'),
            'statuses' => ['active', 'paused', 'cancelled', 'expired'],
        ]);
    }

    public function update(Request $request, ClientSubscription $subscription): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['active', 'paused', 'cancelled', 'expired'])],
            'notes' => ['nullable', 'string'],
        ]);

        if ($data['status'] === 'cancelled' && $subscription->status !== 'cancelled') {
            $data['cancelled_at'] = now();
        }

        $subscription->update($data);

        return back()->with('success', 'Subscription updated.');
    }
}

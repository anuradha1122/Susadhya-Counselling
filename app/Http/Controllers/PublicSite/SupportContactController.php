<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Http\Requests\PublicSite\StoreSupportTicketRequest;
use App\Services\Support\SupportTicketService;
use Illuminate\Http\RedirectResponse;

class SupportContactController extends Controller
{
    public function store(
        StoreSupportTicketRequest $request,
        SupportTicketService $support
    ): RedirectResponse {
        $support->createGuest(
            $request->validated()
        );

        return back()->with(
            'success',
            'Your message has been received. Our support team will review it as soon as possible.'
        );
    }
}

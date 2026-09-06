<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Support\StoreSupportTicketReplyRequest;
use App\Models\SupportTicket;
use App\Services\Support\SupportTicketService;
use Illuminate\Http\RedirectResponse;

class SupportTicketReplyController extends Controller
{
    public function store(
        StoreSupportTicketReplyRequest $request,
        SupportTicket $ticket,
        SupportTicketService $support
    ): RedirectResponse {
        $data =
            $request->validated();

        $support->addAdminReply(
            ticket: $ticket,
            user: $request->user(),
            body: $data['body'],
            isInternal: $data[
                'is_internal'
            ]
        );

        return back()->with(
            'success',
            $data['is_internal']
                ? 'Internal note added.'
                : 'Reply sent to the client.'
        );
    }
}

<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\Support\StoreSupportTicketReplyRequest;
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
        abort_unless(
            (int) $ticket->requester_id
                === (int) $request
                    ->user()
                    ->id,
            404
        );

        $support->addClientReply(
            ticket: $ticket,
            user: $request->user(),
            body: $request->validated(
                'body'
            )
        );

        return back()->with(
            'success',
            'Your reply has been added.'
        );
    }
}

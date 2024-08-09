<?php

namespace App\Http\Controllers;

use App\Http\Requests\TicketReplyRequest;
use App\Models\Services\MailService;
use App\Models\Services\TicketReplyService;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class TicketReplyController extends Controller
{
    use ApiResponseTrait;

    protected TicketReplyService $ticketReplyService;

    public function __construct(TicketReplyService $ticketReplyService)
    {
        $this->ticketReplyService = $ticketReplyService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TicketReplyRequest $request, Ticket $ticket): \Illuminate\Http\JsonResponse
    {
        if ($ticket->is_resolved == 1) {
            return $this->failure('Ticket already closed', 400);
        }
        try {
            $ticketReply = $this->ticketReplyService->store($request, $ticket);

            $ticketReply->load('from', 'to');
            $ticketReply->refresh();
            // Send Email to All Admin
            $mail = new MailService();
            $mail->ticketReplyMail($ticket, $ticketReply);

            return $this->success('Ticket Reply Created', $ticketReply);
        } catch (\Exception $e) {
            return $this->failure($e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(TicketReply $ticketReply)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TicketReply $ticketReply)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TicketReply $ticketReply)
    {
        //
    }
}

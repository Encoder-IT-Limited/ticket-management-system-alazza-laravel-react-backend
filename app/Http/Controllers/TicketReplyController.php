<?php

namespace App\Http\Controllers;

use App\Models\Services\TicketReplyService;
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
        $ticketReplies = $this->ticketReplyService->getAll();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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

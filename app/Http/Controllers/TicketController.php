<?php

namespace App\Http\Controllers;

use App\Http\Requests\TicketRequest;
use App\Http\Resources\Ticket\TicketCollection;
use App\Http\Resources\Ticket\TicketResource;
use App\Mail\TicketCloseMail;
use App\Mail\TicketOpenMail;
use App\Models\Services\MailService;
use App\Models\Services\TicketService;
use App\Models\Ticket;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use App\Traits\CommonTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class TicketController extends Controller
{
    use ApiResponseTrait, CommonTrait;

    protected TicketService $ticketService;

    public function __construct(TicketService $ticketService)
    {
        $this->ticketService = $ticketService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): \Illuminate\Http\JsonResponse
    {
        $tickets = $this->ticketService->getAll();
        return $this->success('Success', TicketCollection::make($tickets));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TicketRequest $request): \Illuminate\Http\JsonResponse
    {
        try {
            $ticket = $this->ticketService->store($request);
            $ticket->load('client');

            // Send Email to All Admin
            $mail = new MailService();
            $mail->ticketOpenMail($ticket);

            return $this->success('Ticket created successfully', new TicketResource($ticket));
        } catch (\Exception $e) {
            return $this->failure($e->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Ticket $ticket): \Illuminate\Http\JsonResponse
    {
        $ticket->load('client', 'admin');
        return $this->success('Success', new TicketResource($ticket));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TicketRequest $request, Ticket $ticket): \Illuminate\Http\JsonResponse
    {
        if ($ticket->is_resolved == 1) {
            return $this->failure('Ticket already closed! Cannot Update Ticket', 400);
        }

        $is_resolved = $ticket->is_resolved;
        $ticket = $this->ticketService->update($request, $ticket);
        $ticket->load('client', 'admin');

        // Send Email ...
        if ($is_resolved == 0 && $ticket->is_resolved == 1) {
            $mail = new MailService();
            $mail->ticketCloseMail($ticket);
        }
        return $this->success('Ticket updated successfully', new TicketResource($ticket));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ticket $ticket): \Illuminate\Http\JsonResponse
    {
        $ticket->delete();
        return $this->success('Ticket deleted successfully');
    }

    public function resolved(Ticket $ticket): \Illuminate\Http\JsonResponse
    {
        if ((auth()->user()->role !== 'admin')) {
            return $this->failure('You are not authorized to perform this action', 403);
        }
//        if ($ticket->is_resolved == 1) {
//            return $this->failure('Ticket already closed', 400);
//        }
        $this->ticketService->resolved($ticket);
        $mail = new MailService();
        $mail->ticketCloseMail($ticket);
        return $this->success('Ticket resolved successfully');
    }

    public function export(Request $request): \Illuminate\Http\Response|string|\Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        if ((auth()->user()->role !== 'admin')) {
            return $this->failure('You are not authorized to perform this action', 403);
        }
        return $this->ticketService->export($request);
    }
}

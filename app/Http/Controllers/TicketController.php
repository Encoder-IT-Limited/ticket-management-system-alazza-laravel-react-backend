<?php

namespace App\Http\Controllers;

use App\Http\Requests\TicketRequest;
use App\Http\Resources\Ticket\TicketCollection;
use App\Http\Resources\Ticket\TicketResource;
use App\Mail\TicketCloseMail;
use App\Mail\TicketOpenMail;
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
//            $users = User::where('role', 'admin')->get();
//            Mail::to($users)->send(new TicketOpenMail($ticket));
            $users = User::where('role', 'admin')->get();
            foreach ($users as $user) {
                Mail::to($user->email)->queue(new TicketOpenMail($ticket, $user));
            }

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
        if ($ticket->status == 0) {
            return $this->failure('Ticket already closed', 400);
        }

        $status = $ticket->status;
        $ticket = $this->ticketService->update($request, $ticket);
        $ticket->load('client', 'admin');

        // Send Email ...
        if ($status == 1 && $ticket->status == 0) {
            $users = User::where('role', 'admin')->get();
            foreach ($users as $user) {
                Mail::to($user->email)->queue(new TicketCloseMail($ticket, $user));
            }
            if ($ticket->client->email) {
                Mail::to($ticket->client->email)->queue(new TicketCloseMail($ticket, $ticket->client));
            }
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
}

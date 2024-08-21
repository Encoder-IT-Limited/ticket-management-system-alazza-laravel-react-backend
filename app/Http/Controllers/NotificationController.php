<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    use ApiResponseTrait;

    public function newTickets(): void
    {
        $user = auth()->user();

        $ticketCount = Ticket::where('admin_id', null)->count();
        $this->success('Success', ['ticket_count' => $ticketCount]);
    }
}

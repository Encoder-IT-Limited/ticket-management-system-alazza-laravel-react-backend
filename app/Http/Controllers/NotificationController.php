<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    use ApiResponseTrait;

    public function newTickets(): \Illuminate\Http\JsonResponse
    {
        $user = auth()->user();

        $ticketCount = Ticket::where('admin_id', null)->where('is_resolved', false)->count();
        return $this->success('Success', ['ticket_count' => $ticketCount]);
    }
}

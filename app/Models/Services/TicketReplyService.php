<?php

namespace App\Models\Services;

use App\Models\Ticket;
use App\Models\TicketReply;
use App\Traits\ApiResponseTrait;
use App\Traits\CommonTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TicketReplyService
{
    use CommonTrait;


    public function getAll()
    {
        $query = request('search_query');
        return TicketReply::latest()
            ->with('from', 'to')
            ->paginate(request('per_page', 25));
    }

    public function store($request, $ticket): TicketReply
    {
        $data = $request->validated();
        $data['ticket_id'] = $ticket->id;
        $data['from_id'] = auth()->id();
        if ($ticket->client_id === auth()->id()) {
            $data['to_id'] = $ticket->admin_id;
        } else {
            $data['to_id'] = $ticket->client_id;
        }
        $ticketReply = new TicketReply();
        $ticketReply->fill($data);
        $ticketReply->save();

        return $ticketReply;
    }

    public function update($request, $ticketReply)
    {
        $data = $request->validated();
        $ticketReply->fill($data);
        $ticketReply->save();

        return $ticketReply;
    }

    public function delete($ticketReply): void
    {
        $ticketReply->delete();
    }
}

<?php

namespace App\Models\Services;


use App\Models\Ticket;

class TicketService
{

    public function getAll()
    {
        $query = request('search_query');
        return Ticket::whereAny(['title', 'description']
            , 'like'
            , "%$query%")
            ->with('client', 'admin')
            ->latest()
            ->paginate(request('per_page', 25));
    }

    public function store($request): Ticket
    {
        $data = $request->validated();
        $data['client_id'] = auth()->id();
        $ticket = new Ticket();
        $ticket->fill($data);
        $ticket->save();

        return $ticket;
    }

    public function update($request, $ticket)
    {
        $data = $request->validated();
        $data['resolved_at'] = $data['is_resolved'] ? now() : null;
        $data['status'] = $data['is_resolved'] ? 'closed' : 'open';
        $ticket->fill($data);
        $ticket->save();

        return $ticket;
    }
}

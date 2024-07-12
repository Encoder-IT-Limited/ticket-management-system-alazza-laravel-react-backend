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

    public function resolved($ticket): void
    {
        $ticket->update([
            'is_resolved' => true,
            'resolved_at' => now(),
            'admin_id' => auth()->user()->role === 'admin' ? auth()->id() : null,
            'status' => 'closed',
        ]);
    }

    public function update($request, $ticket)
    {
        $data = $request->validated();
        if (isset($data['is_resolved'])) {
            $data['resolved_at'] = $data['is_resolved'] ? now() : null;
            $data['status'] = $data['is_resolved'] ? 'closed' : 'open';
            $data['admin_id'] = $data['is_resolved'] ? auth()->id() : null;
            $data['is_resolved'] = $data['is_resolved'] ? 1 : 0;
        }
        $ticket->fill($data);
        $ticket->save();

        return $ticket;
    }
}

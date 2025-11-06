<?php

namespace App\Http\Resources\Ticket;

use App\Models\Ticket;
use App\Traits\MetaResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TicketCollection extends ResourceCollection
{
    use MetaResponseTrait;

    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        $opened = Ticket::where('status', 'open');
        $closed = Ticket::where('status', 'closed');
        if (auth()->user()->role->name == 'Client') {
            $opened->where('client_id', auth()->id());
            $closed->where('client_id', auth()->id());
        }
        return [
            'data' => $this->collection->transform(function ($user) {
                return TicketResource::make($user);
            }),
            'open_tickets' => $opened->count(),
            'closed_tickets' => $closed->count(),
            'meta' => $this->generateMeta(),
        ];
    }
}

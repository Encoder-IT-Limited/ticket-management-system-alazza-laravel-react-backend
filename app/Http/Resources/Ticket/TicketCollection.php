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
        return [
            'data' => $this->collection->transform(function ($user) {
                return TicketResource::make($user);
            }),
            'open_tickets' => Ticket::where('status', 'open')->count(),
            'closed_tickets' => Ticket::where('status', 'closed')->count(),
            'meta' => $this->generateMeta(),
        ];
    }
}

<?php

namespace App\Http\Resources\Ticket;

use App\Http\Resources\MediaResource;
use App\Http\Resources\TicketReplyResource;
use App\Http\Resources\User\UserResource;
use App\Http\Resources\User\UserWithoutMediaResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'ticket_no' => $this->ticket_no,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'is_resolved' => $this->is_resolved,
            'resolved_at' => $this->resolved_at,
            'files' => $this->whenLoaded('media', MediaResource::collection($this->media)),
            'replies' => TicketReplyResource::collection($this->whenLoaded('ticketReplies')),
            'client' => new UserWithoutMediaResource($this->whenLoaded('client')),
            'admin' => new UserWithoutMediaResource($this->whenLoaded('admin')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

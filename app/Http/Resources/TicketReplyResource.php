<?php

namespace App\Http\Resources;

use App\Http\Resources\User\UserWithoutMediaResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use function Pest\Laravel\from;

class TicketReplyResource extends JsonResource
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
            'ticket_id' => $this->ticket_id,
            'from' => new UserWithoutMediaResource($this->whenLoaded('from')),
            'to' => new UserWithoutMediaResource($this->whenLoaded('to')),
            'message' => $this->message,
            'position' => $this->position,
            'user_type' => $this->user_type,
            'read_at' => $this->read_at,
            'replied_at' => $this->replied_at,
            'created_at' => $this->created_at,
        ];
    }
}

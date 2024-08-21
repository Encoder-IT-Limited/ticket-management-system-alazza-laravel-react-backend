<?php

namespace App\Http\Resources\User;

use App\Http\Resources\MediaResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'status' => $this->status,
            'role' => $this->role,
            'company' => $this->company,
            'section' => $this->section,
            'position' => $this->position,
            'documents' => $this->whenLoaded('media', [
                'user_id_documents' => MediaResource::collection($this->media->where('collection_name', 'user_id_document')),
                'device_licenses' => MediaResource::collection($this->media->where('collection_name', 'device_license')),
                'other_documents' => MediaResource::collection($this->media->where('collection_name', 'other_document')),
            ]),
//            'documents' => [
//                'user_id_documents' => $this->media->where('collection_name', 'user_id_document')->pluck('media_url'),
//                'device_licenses' => $this->media->where('collection_name', 'device_license')->pluck('media_url'),
//                'other_documents' => $this->media->where('collection_name', 'other_document')->pluck('media_url'),
//            ],
            'created_at' => $this->created_at,
        ];
    }
}

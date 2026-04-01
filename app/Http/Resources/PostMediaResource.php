<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostMediaResource extends JsonResource
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
            'media_type' => $this->media_type,
            'file_url' => $this->file_url, // Uses accessor from model
            // 'thumbnail_url' => $this->thumbnail_url, // Uses accessor from model
            'file_size' => $this->file_size ? $this->file_size . ' KB' : null,
            'mime_type' => $this->mime_type,
            'order' => $this->order,
        ];
    }
}

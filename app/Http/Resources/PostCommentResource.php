<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostCommentResource extends JsonResource
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
            'comment' => $this->comment,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),

            // User information
            'user' => [
                'id' => $this->user->id,
                'first_name' => $this->user->first_name,
                'last_name' => $this->user->last_name,
                'name' => $this->user->first_name . ' ' . $this->user->last_name,
                'image' => $this->user->image ? asset($this->user->image) : null,
                'type' => 'User',
            ],

            // Nested replies (2-level only)
            'replies' => PostCommentResource::collection($this->whenLoaded('replies')),
            'replies_count' => $this->when($this->relationLoaded('replies'), function () {
                return $this->replies->count();
            }, 0),

            // Likes information
            'likes_count' => $this->likes()->count(),
            'user_liked' => $this->likes()->where('user_id', $request->user()->id)->exists(),
        ];
    }
}

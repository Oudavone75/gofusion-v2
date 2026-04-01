<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * This resource extends PostResource data with additional comments list
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'content' => $this->content,
            'status' => $this->status->value,
            'published_at' => $this->published_at,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'user_reacted' => $this->reactions()->where('user_id', $request->user()->id)->exists(),

            // Author information (polymorphic)
            'author' => [
                'id' => $this->author->id,
                'first_name' => $this->author->first_name,
                'last_name' => $this->author->last_name,
                'name' => class_basename($this->author_type) == 'User' ? $this->author->first_name . ' ' . $this->author->last_name : 'Go Fusion Admin',
                'image' => class_basename($this->author_type) == 'User' ? (asset($this->author->image) ?? null) : asset('go-fusion.png'),
                'type' => class_basename($this->author_type),
            ],

            // Media attachments
            'media' => PostMediaResource::collection($this->whenLoaded('media')),

            // Counts
            'reactions_count' => $this->reactions()->count(),
            'comments_count' => $this->comments()->count(),

            // Reaction breakdown
            'reactions_summary' => $this->reactions()
                ->selectRaw('reaction_type, count(*) as count')
                ->groupBy('reaction_type')
                ->get()
                ->pluck('count', 'reaction_type'),

            // Comments list (only for detail view, not listing)
            'comments' => PostCommentResource::collection($this->whenLoaded('comments')),
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use phpDocumentor\Reflection\Types\This;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $author = null;
        if($this->author) {
            $author = [
                'id' => $this->author->id,
                'first_name' => $this->author->first_name,
                'last_name' => $this->author->last_name,
                'name' => class_basename($this->author_type) == 'User' ? $this->author->first_name . ' ' . $this->author->last_name : 'Go Fusion Admin',
                'image' => class_basename($this->author_type) == 'User' ? ($this->author->is_admin == 1 ? asset($this->author?->company?->image) : asset($this->author?->image)) : asset('go-fusion.png'),
                'type' => class_basename($this->author_type), // 'User' or 'Admin'
            ];
        }
        return [
            'id' => $this->id,
            'content' => $this->content,
            'status' => $this->status->value, // Get the string value from enum
            'published_at' => $this->published_at,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'user_reacted' =>  $this->reactions()->where('user_id', $request->user()->id)->exists(),
            'author' => $author,
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
        ];
    }
}

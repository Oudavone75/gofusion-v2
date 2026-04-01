<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class ThemeChallengeResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'points' => $this->attempted_points,
            'is_global' => $this->is_global,
            'logo' => $this->logo ? asset($this->logo) : null,
            'theme' => new ThemeResource($this->whenLoaded('theme')),
            'category' => new ChallengeCategoryResource($this->whenLoaded('category')),
            'company' => new CompanyResource($this->whenLoaded('company'), Auth::user()->company->mode->name),
            'is_attempted' => $this->challengePoints()->where('user_id', Auth::id())->exists(),
            'image_path' => $this->image_path ?? null,
            'video_url' => $this->video_url ?? null,
            'event' => $this->event ?? null,
            'created_by_me' => Auth::user()?->id === $this->createdBy?->id ? true : false,
            'mode' => $this->mode == 'photo' ? 'image' : $this->mode,
        ];
    }
}

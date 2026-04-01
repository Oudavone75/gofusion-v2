<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class NewsResource extends JsonResource
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
            'category' => $this->category ? [
                'id' => $this->category->id,
                'name' => $this->category->name,
            ] : null,
            'company' => $this->company ? [
                'id' => $this->company->id,
                'name' => $this->company->name,
                'logo' => $this->company->image,
            ] : [
                'name' => 'Go Fusion Admin',
                'logo' => asset('assets/icons/App_Icon.png'),
            ],
            'title' => $this->title,
            'description' => $this->description,
            'image' => $this->image_path,
            'status' => $this->status,
            'published_at' => $this->published_at
                ? (Carbon::parse($this->published_at)->isToday()
                    ? Carbon::parse($this->published_at)->format('h:i A')
                    : Carbon::parse($this->published_at)->format('d M Y'))
                : null,
        ];
    }
}

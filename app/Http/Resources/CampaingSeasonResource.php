<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CampaingSeasonResource extends JsonResource
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
            'reward' => $this->reward,
            'custom_reward' => $this->custom_reward,
            'custom_reward_status' => $this->custom_reward_status,
            'currency' => $this->currency,
            'start_date' => Carbon::parse($this->start_date)->format('d M Y'),
            'end_date' => Carbon::parse($this->end_date)->format('d M Y'),
            'sessions' => GoSessionResource::collection($this->whenLoaded('goSessions'))
        ];
    }
}

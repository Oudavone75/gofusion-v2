<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'        =>  $this->id,
            'user_id'   =>  $this->user_id,
            'type'      =>  $this->type,
            'title'     =>  $this->title,
            'content'   =>  $this->content,
            'data'      =>  $this->data,
            'created_at'=>  formatedDateTime(date: $this->sent_at)
        ];
    }
}

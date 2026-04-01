<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class UserDetailsResource extends JsonResource
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
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'work_email' => $this->work_email,
            'city' => $this->city,
            'dob' => $this->dob,
            'image' => $this->image ? asset($this->image) : "",
            'company' => new CompanyResource($this->whenLoaded('company'), Auth::user()?->company?->mode?->name),
            'department' => new CompanyDepartmentResource($this->whenLoaded('department')),
            'modes' => ModeResource::collection($this->whenLoaded('modes'))
        ];
    }
}

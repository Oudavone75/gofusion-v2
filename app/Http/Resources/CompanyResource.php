<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class CompanyResource extends JsonResource
{
    protected $user_mode;

    public function __construct($resource, $user_mode = null)
    {
        parent::__construct($resource);
        $this->user_mode = $user_mode;
    }
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
            'registration_date' => $this->registration_date,
            'address' => $this->address,
            'departments' => CompanyDepartmentResource::collection($this->whenLoaded('departments')),
            'image' => $this->image ? asset($this->image) : '',
            'mode_name' => $this->whenLoaded('mode') ? $this->mode->name : "",
            'mode_id' => $this->whenLoaded('mode') ? $this->mode->id : null,
            'is_active' => Auth::user()?->company?->mode?->name == $this->user_mode ? true : false
        ];
    }
}

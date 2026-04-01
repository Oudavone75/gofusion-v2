<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SurveyFeedbackQuestionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "question" =>$this->question_text,
            'options' => SurveyFeedbackQuestionOptionResource::collection($this->whenLoaded('options'))
        ];
    }
}

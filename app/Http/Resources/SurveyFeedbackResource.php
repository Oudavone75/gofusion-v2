<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SurveyFeedbackResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $result = [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'questions' => SurveyFeedbackQuestionResource::collection($this->whenLoaded('questions'))
        ];
        if (isset($result['questions']) && $result['questions']->collection) {
            $questions = $this->buildQuestionsArray($result['questions']);
            $result['questions'] = $questions;
        }
        return $result;
    }

    public function buildQuestionsArray($questions)
    {
        $questions_response = [];
        foreach ($questions as $question) {
            $questions_response[] = [
                'id' => $question->id,
                'question' => $question->question_text,
                'options' => collect($question->options)->map(function ($option) {
                    return [
                        'id' => $option->id,
                        'option_text' => $option->option_text,
                    ];
                })->values()
            ];
        }
        return $questions_response;
    }
}

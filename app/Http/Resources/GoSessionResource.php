<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class GoSessionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $stepStatus = $this->getStatus();
        $response =  [
            'id' => $this->id,
            'title' => $this->title,
            'status' => $stepStatus['status'],
            'percentage' => $stepStatus['percentage']
        ];
        if ($request->routeIs('sessions.details')) {
            $response['steps'] = GoSessionStepResource::collection($this->whenLoaded('goSessionSteps'));
        }
        return $response;
    }

    public function getStatus()
    {
        $user = Auth::user();
        $stepAttemptsCount = 0;
        $stepNotCreatedCount = 0;
        foreach ($this?->goSessionSteps as $step) {
            if ($step->position == 1) {
                if ($step?->quizStep?->attempts()->where('user_id', $user->id)->exists()) {
                    $stepAttemptsCount++;
                }
                if (!$step?->quizStep) {
                    $stepNotCreatedCount++;
                }
            }
            if ($step->position == 2) {
                if ($step?->imageSubmissionGuideline?->attempts()->where('user_id', $user->id)->exists()) {
                    $stepAttemptsCount++;
                }
            }
            if ($step->position == 5) {
                if ($step?->spinWheelStep?->attempts()->where('user_id', $user->id)->exists()) {
                    $stepAttemptsCount++;
                }
            }

            if ($step->position == 6) {
                if ($step?->surveyStep?->attempts()->where('user_id', $user->id)->exists()) {
                    $stepAttemptsCount++;
                }
            }
        }
        // not_created: quiz step (position 1) exists in session but has no quiz created
        // not_started: all steps are created but none attempted
        // in_progress: all steps created, some attempted
        // completed:   all steps attempted
        if ($stepNotCreatedCount > 0) {
            $status = 'not_created';
        } elseif ($stepAttemptsCount === $this?->goSessionSteps?->count()) {
            $status = 'completed';
        } elseif ($stepAttemptsCount > 0) {
            $status = 'in_progress';
        } else {
            $status = 'not_started';
        }

        return [
            'status' => $status,
            'percentage' => $this?->goSessionSteps?->count() > 0 ? round(($stepAttemptsCount / 4) * 100) : 0
        ];
    }
}

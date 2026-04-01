<?php

namespace App\Http\Resources;

use App\Models\ChallengeStep;
use App\Models\EventSubmissionStep;
use App\Models\GoSessionStep;
use App\Models\GoUserProgress;
use App\Models\ImageSubmissionStep;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\SpinWheelSubmissionStep;
use App\Models\SurvayFeedbackAttempt;
use App\Models\ValidateLaterStep;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class GoSessionStepResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $position = $this->position;
        if ($position === 5) {
            $position = 3;
        }
        if ($position == 6) {
            $position = 4;
        }
        $user = Auth::user();
        $data = [
            'id' => $this->id,
            'title' => $this->title,
            'position' => $position,
            'is_complete' => $this->getIsCompleteStep($this->id),
            'is_locked' => $this->getIsLockedStep($this->position, $this),
        ];
        $is_complete_check = $this->getIsCompleteStep($this->id);
        if ($user) {
            $earn_reward = $this->getStepEarnReward($this, $user);
            $data["earn_step_point"] = floatval($earn_reward);
            $data["is_validate_later"] = $is_complete_check === true ? false :  $this->getValidateLaterStepExist(user_id: $user->id, go_session_step_id: $this->id);
        }
        return $data;
    }

    public function getIsCompleteStep($step_id)
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }
        return GoUserProgress::where('go_session_step_id', $step_id)
            ->where('user_id', $user->id)->exists();
    }

    public function getIsLockedStep($position, $step_object)
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }
        $is_complete_step = $this->getIsCompleteStep($step_object->id);
        $find_step = GoSessionStep::where('go_session_id', $step_object->go_session_id)
            ->where('position', '!=', 3)
            ->where('position', '!=', 4)
            ->where('position', $position - 1)->first();
        if (!$find_step && $position === 5) {
            $find_step = GoSessionStep::where('go_session_id', $step_object->go_session_id)
                ->where('position', 2)->first();
        }
        if (!$find_step && $position === 6) {
            $find_step = GoSessionStep::where('go_session_id', $step_object->go_session_id)
                ->where('position', 3)->first();
        }
        $is_last_step_completed = false;
        $is_validate_later = false;
        if ($find_step) {
            $is_last_step_completed = $this->getIsCompleteStep($find_step->id);
            $validate_later = $this->getValidateLaterStepExist(user_id: $user->id, go_session_step_id: $find_step->id);
            if ($validate_later) {
                $is_validate_later = true;
            }
        }
        if ($position === 1 || $is_complete_step === true || $is_last_step_completed === true || $is_validate_later === true ) {
            return false;
        }
        return true;
    }

    public function getValidateLaterStepExist($user_id, $go_session_step_id)
    {
        return ValidateLaterStep::where('user_id', $user_id)
            ->where('go_session_step_id', $go_session_step_id)->exists();
    }

    public function getStepEarnReward($go_session_step, $user)
    {
        $step_handlers = [
            1 => [Quiz::class, QuizAttempt::class, 'quiz_id'],
            2 => [ImageSubmissionStep::class, null],
            3 => [EventSubmissionStep::class, null],
            4 => [ChallengeStep::class, null],
            5 => [SpinWheelSubmissionStep::class, null],
            6 => [SurvayFeedbackAttempt::class, null],
        ];

        $position = $go_session_step->position;

        if (!isset($step_handlers[$position])) {
            return 0;
        }

        [$main_model, $related_model, $foreign_key] = array_pad($step_handlers[$position], 3, null);

        if ($position === 1 && $related_model) {
            $quiz = $main_model::where('go_session_step_id', $go_session_step->id)->first();
            if (!$quiz) return 0;
            $attempt = $related_model::where('user_id', $user->id)
                ->where($foreign_key, $quiz->id)
                ->first();

            return $attempt->points ?? 0;
        }

        $step = $main_model::where('go_session_step_id', $go_session_step->id)
            ->where('user_id', $user->id)
            ->first();

        return $step->points ?? 0;
    }
}

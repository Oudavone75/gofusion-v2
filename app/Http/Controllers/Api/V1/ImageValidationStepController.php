<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use App\Services\ImageValidationStepService;
use App\Http\Requests\ImageStepDetailsRequest;
use App\Http\Requests\UploadChallengeImageRequest;
use App\Http\Requests\ValidateStepImageRequest;
use Illuminate\Http\Request;

class ImageValidationStepController extends Controller
{
    use ApiResponse;
    public function __construct(public ImageValidationStepService $imageValidationStepService) {}

    public function getImageStepDetails(ImageStepDetailsRequest $request)
    {
        try {
            $image_step_details = $this->imageValidationStepService->getImageStepDetails($request->all());
            if ($image_step_details['success'] === false) {
                return $this->error(
                    status: false,
                    message: $image_step_details['message']
                );
            }
            return $this->success(status: true, message: $image_step_details['message'], result: $image_step_details['data']);
        } catch (\Throwable $th) {
            return $this->error(status: false, message: $th->getMessage());
        }
    }

    public function uploadStepImage(UploadChallengeImageRequest $request)
    {
        try {
            $image = $this->imageValidationStepService->uploadStepImage($request);
            if ($image['success'] === false) {
                return $this->error(status: false, message: $image['message']);
            }
            return $this->success(status: true, message: $image['message'], result: $image['data']);
        } catch (\Throwable $th) {
            return $this->error(status: false, message: $th->getMessage());
        }
    }

    public function validateStepImage(ValidateStepImageRequest $request)
    {
        try {
            $validate_image = $this->imageValidationStepService->validateStepImage($request);
            if ($validate_image['success'] === false){
                return $this->error(status: false, message: $validate_image['message']);
            }
            return $this->success(status: true, message: $validate_image['message'], result: $validate_image['data']);
        } catch (\Throwable $th) {
            return $this->error(status: false, message: $th->getMessage());
        }
    }

    public function appealForManualValidate(Request $request)
    {
        try {
            $request->validate([
                'go_session_step_id' => 'required|exists:go_session_steps,id',
            ]);
            $validate_image = $this->imageValidationStepService->appealForManualValidate($request);
            if ($validate_image['success'] === false){
                return $this->error(status: false, message: $validate_image['message']);
            }
            return $this->success(status: true, message: $validate_image['message'], result: $validate_image['data']);
        } catch (\Throwable $th) {
            return $this->error(status: false, message: $th->getMessage());
        }
    }
}

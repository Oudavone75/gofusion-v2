<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ValidateLaterStepRequest;
use App\Traits\ApiResponse;

class ValidateLaterStepController extends Controller
{
    use ApiResponse;
    public function validateLaterStep(ValidateLaterStepRequest $request)
    {
        try {
            $request_data = $request->all();
        } catch (\Throwable $th) {
            return $this->error(status: false, message: $th->getMessage());
        }
    }
}

<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\CarbonFootprintService;
use App\Traits\ApiResponse;
use App\Http\Requests\CarbonFootprintRequest;
use Illuminate\Http\Request;

class CarbonFootprintController extends Controller
{
    use ApiResponse;

    public function __construct(private CarbonFootprintService $carbon_footprint_service) {}

    public function saveCarbonFootprints(CarbonFootprintRequest $request)
    {
        try {
            $response = $this->carbon_footprint_service->saveCarbonFootprints($request->validated());
            if ($response['success'] === false) {
                return $this->error(status: false, message: $response['message'], code: 400);
            }
            return $this->success(status: true, message: $response['message'], result: $response['data']);
        } catch (\Throwable $e) {
            return $this->error(false, $e->getMessage());
        }
        return $this->success(status: true, message: $response['message'], result: $response['data']);
    }

    public function getCurrentMonthCarbonFootprint()
    {
        try {
            $userId = auth()->id();
            $carbon_foot_print_record = $this->carbon_footprint_service->getCurrentMonthCarbonFootprint($userId, 'record');
            if (!$carbon_foot_print_record) {
                return $this->success(true, trans('general.carbon_record_not_found'), [], 200);
            }
            return $this->success(true, trans('general.carbon_record'), $carbon_foot_print_record);
        } catch (\Throwable $e) {
            return $this->error(false, $e->getMessage());
        }
    }

    public function saveCarbonFootprintValues(Request $request)
    {
        try {
            $request->validate([
                'carbon_unit' => 'required|string',
                'carbon_value' => 'required|string',
                'water_unit' => 'required|string',
                'water_value' => 'required|string',
                'token' => 'required|string',
            ]);
            $response = $this->carbon_footprint_service->saveCarbonFootprints($request->all());
            return $this->success(true, $response['message'], $response ['data']);
        } catch (\Throwable $e) {
            return $this->error(false, $e->getMessage());
        }
    }
}

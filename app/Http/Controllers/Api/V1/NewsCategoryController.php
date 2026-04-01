<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\NewsService;
use App\Traits\ApiResponse;

class NewsCategoryController extends Controller
{
    use ApiResponse;

    public function __construct(private NewsService $news_service)
    {
        $this->news_service = $news_service;
    }

    /**
     * Display a listing of the resource.
     */
    public function getNewsCategoryList()
    {
        try {
            $news_categories = $this->news_service->getNewsCategoryList();
            if ($news_categories['success'] === false) {
                return $this->error(status: false, message: $news_categories['message']);
            }
            return $this->success(status: true, message: $news_categories['message'], result: $news_categories['data']);
        } catch (\Throwable $th) {
            return $this->error(status: false, message: $th->getMessage());
        }
    }
}

<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use App\Services\NewsService;
use App\Traits\AppCommonFunction;

class NewsController extends Controller
{
    use ApiResponse, AppCommonFunction;

    public function __construct(private NewsService $news_service)
    {
        $this->news_service = $news_service;
    }

    /**
     * Display a listing of the resource.
     */
    public function getNewsList()
    {
        try {
            $params = [
                'category' => $this->fetchQueryParam(param: 'category'),
                'search'   => $this->fetchQueryParam(param: 'search'),
            ];
            $news = $this->news_service->getNewsList($params);
            return $this->success(status: true, message: $news['message'], result: $news['data']);
        } catch (\Throwable $th) {
            return $this->error(status: false, message: $th->getMessage());
        }
    }

    public function getNewsDetail($news_id)
    {
        try {
            $news = $this->news_service->getNewsDetail($news_id);
            return $this->success(status: true, message: $news['message'], result: $news['data']);
        } catch (\Throwable $th) {
            return $this->error(status: false, message: $th->getMessage());
        }
    }
}

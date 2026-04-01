<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\AppCommonFunction;
use App\Traits\ApiResponse;
use App\Services\NewsService;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\NewsFeedRequest;
use App\Models\News;

class NewsFeedController extends Controller
{
    use AppCommonFunction, ApiResponse;
    protected $views_directory = 'admin.news-feed.';
    protected $route_directory = 'admin.news-feed.';
    public function __construct(private NewsService $news_service) {}
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $news_feeds = $this->news_service->getNewsFeedList();
        return view($this->views_directory . 'index', compact('news_feeds'));
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $companies = $this->getCompanies();
        $categories = $this->news_service->getNewsCategoryList();

        return view($this->views_directory . 'create', compact('companies', 'categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(NewsFeedRequest $request)
    {
        try {
            $company_id = $request->company;
            $this->news_service->createNewsFeed($request, $company_id, 'admin');
            return response()->json([
                'redirect' => route($this->route_directory . 'index'),
                'message' => 'News Feed created successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(News $news_feed)
    {
        $news_feed = $this->news_service->getNewsFeed($news_feed->id);
        return view($this->views_directory . 'view', compact('news_feed'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(News $news_feed)
    {
        $companies = $this->getCompanies();
        $categories = $this->news_service->getNewsCategoryList();

        return view($this->views_directory . 'edit', compact('news_feed', 'companies', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(NewsFeedRequest $request, string $id)
    {
        try {
            $company_id = $request->company;
            $this->news_service->updateNewsFeed($request, $id, $company_id);
            return response()->json([
                'redirect' => route($this->route_directory . 'index'),
                'message' => 'News Feed updated successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(News $news_feed)
    {
        try {
            $this->news_service->deleteNewsFeed($news_feed->id);
            return response()->json(['success' => true, 'message' => 'News Feed deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function toggleStatus(Request $request, $news_id)
    {
        try {
            $response = $this->news_service->toggleStatus($request->all(), $news_id);
            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}

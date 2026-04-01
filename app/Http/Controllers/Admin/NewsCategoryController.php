<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\AppCommonFunction;
use App\Http\Requests\NewsFeedRequest;
use App\Models\News;
use App\Models\NewsCategory;
use App\Services\NewsCategoryService;
use App\Traits\ApiResponse;
use Illuminate\Validation\ValidationException;

class NewsCategoryController extends Controller
{
    use AppCommonFunction, ApiResponse;
    protected $views_directory = 'admin.news-category.';
    protected $route_directory = 'admin.news-category.';
    public function __construct(private NewsCategoryService $news_category_service) {}
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $news_categories = $this->news_category_service->getNewsCategoryList();
        return view($this->views_directory . 'index', compact('news_categories'));
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view($this->views_directory . 'create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required',
            ]);
            $company_id = $request->company_id;
            $this->news_category_service->createNewsCategory($request, $company_id);
            return response()->json([
                'redirect' => route($this->route_directory . 'index'),
                'message' => 'News Category created successfully!'
            ]);
        } catch (ValidationException $e) {
            return response()->json($e->errors(), 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(NewsCategory $news_category)
    {
        $news_category = $this->news_category_service->getNewsCategory($news_category->id);
        return view($this->views_directory . 'view', compact('news_category'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(NewsCategory $news_category)
    {
        return view($this->views_directory . 'edit', compact('news_category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $request->validate([
                'name' => 'required',
            ]);
            $this->news_category_service->updateNewsCategory($request, $id);
            return response()->json([
                'redirect' => route($this->route_directory . 'index'),
                'message' => 'News Category updated successfully!'
            ]);
        }
        catch (ValidationException $e) {
            return response()->json($e->errors(), 422);
        }
        catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(NewsCategory $news_category)
    {
        try {
            $this->news_category_service->deleteNewsCategory($news_category->id);
            return response()->json(['success' => true, 'message' => 'News Category deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}

<?php

namespace App\Services;

use App\Http\Resources\NewsResource;
use App\Models\NewsCategory;
use App\Models\News;
use App\Traits\AppCommonFunction;

class NewsCategoryService
{
    use AppCommonFunction;
    public function getNewsCategoryList($company_id = null)
    {

        $query = NewsCategory::select('id', 'name', 'created_at')->orderBy('created_at', 'desc');
        // if ($company_id) {
        //     $query->where('company_id', $company_id);
        // } else {
        //     $query->where('company_id', null);
        // }

        return $this->getPaginatedData($query);
    }

    public function getNewsDetail($id)
    {
        $news_category = News::find($id);

        if (!$news_category) {
            return [
                'success' => false,
                'message' => 'News not found.',
                'data' => [],
            ];
        }

        return [
            'success' => true,
            'message' => 'News fetched successfully.',
            'data' => new NewsResource($news_category)
        ];
    }

    public function createNewsCategory($request, $company_id)
    {
        $request->merge(['company_id' => $company_id]);
        NewsCategory::create($request->all());
        return true;
    }

    public function updateNewsCategory($request, $news_category_id)
    {
        $news_category = NewsCategory::find($news_category_id);
        if (!$news_category) {
            return ['success' => false, 'message' => 'News Category item not found.', 'data' => []];
        }

        $data = [
            'name' => $request->name
        ];
        $news_category->update($data);

        return true;
    }

    public function getNewsCategory($newsId)
    {
        $news_category = NewsCategory::find($newsId);
        if (!$news_category) {
            return ['success' => false, 'message' => 'News Category not found.', 'data' => []];
        }
        return $news_category;
    }

    public function deleteNewsCategory($newsId)
    {
        $news_category = NewsCategory::find($newsId);
        if (!$news_category) {
            return ['success' => false, 'message' => 'News Category not found.', 'data' => []];
        }
        $news_category->delete();
        return true;
    }
}

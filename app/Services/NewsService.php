<?php

namespace App\Services;

use App\Http\Resources\NewsResource;
use App\Models\NewsCategory;
use App\Models\News;
use App\Traits\AppCommonFunction;
use Illuminate\Support\Facades\Auth;

class NewsService
{
    use AppCommonFunction;

    public function getNewsCategoryList($company_id = null)
    {

        $query = NewsCategory::select('id', 'name')->orderBy('created_at', 'desc');

        // if (request()->is('api/*')) {
        //     $user = Auth::user();
        //     $company_id = $user->isEmployee() ? $user->company_id : null;
        // } else {
        //     $company_id = $company_id ?? null;
        // }

        // $query->where('company_id', $company_id);

        $news_categories = $query->get();

        if ($news_categories->isEmpty()) {
            return ['success' => false, 'message' => trans('general.news_category_not_found'), 'data' => []];
        }

        return [
            'success' => true,
            'message' => trans('general.news_category_fetched'),
            'data' => $news_categories
        ];
    }

    public function getNewsList($params)
    {
        $news = News::orderBy('created_at', 'desc');

        if (!is_null($params['search'])) {
            $search = $params['search'];

            $news = $news->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%$search%")
                    ->orWhere('description', 'LIKE', "%$search%")
                    ->orWhereHas('category', function ($q3) use ($search) {
                        $q3->where('name', 'LIKE', "%$search%");
                    })
                    ->orWhereHas('company', function ($q3) use ($search) {
                        $q3->where('name', 'LIKE', "%$search%");
                    });
            });
        }

        if (!empty($params['category'])) {
            $categories = explode(',', $params['category']);

            $news = $news->whereHas('category', function ($query) use ($categories) {
                $query->whereIn('name', $categories);
            });
        }
        $user = Auth::user();
        $news = $news->when($user->isEmployee(), function ($query) use ($user) {
            return $query->where(function ($q) use ($user) {
                $q->whereNull('company_id')
                ->orWhere('company_id', $user->company_id);
            });
        }, function ($query) {
            return $query->whereNull('company_id');
        });

        $news = $news->where('status', 'active')->get();

        if ($news->isEmpty()) {
            return ['success' => false, 'message' => trans('general.news_listing_not_found'), 'data' => []];
        }

        return [
            'success' => true,
            'message' => trans('general.news_listing_fetched'),
            'data' => NewsResource::collection($news)
        ];
    }

    public function getNewsDetail($id)
    {
        $news = News::find($id);

        if (!$news) {
            return [
                'success' => false,
                'message' => 'News not found.',
                'data' => [],
            ];
        }

        return [
            'success' => true,
            'message' => 'News fetched successfully.',
            'data' => new NewsResource($news)
        ];
    }

    public function getNewsFeedList($company_id = null)
    {
        $query = News::with(['category', 'company'])->orderBy('created_at', 'desc');
        if ($company_id) {
            $query->where('company_id', $company_id);
        }
        return $this->getPaginatedData($query);
    }

    public function createNewsFeed($request, $company_id, $guard)
    {
        $filename = uploadFile($request->file('image'), 'public', 'news-feed');
        if (!$filename) {
            return ['success' => false, 'message' => 'Failed to upload image.', 'data' => []];
        }

        $imagePath = asset('storage/news-feed/' . $filename);

        $request->merge([
            'company_id' => $company_id,
            'created_by'    => $guard === 'web' ? Auth::guard($guard)->id() : null,
            'category_id' => $request->category,
            'image_path' => $imagePath,
            'published_at' => now()
        ]);

        News::create($request->all());

        return true;
    }

    public function updateNewsFeed($request, $newsId, $company_id)
    {
        $news = News::find($newsId);
        if (!$news) {
            return ['success' => false, 'message' => 'News item not found.', 'data' => []];
        }

        $data = [
            'title'         => $request->title,
            'description'   => $request->description,
            'category_id'   => $request->category,
            'company_id'    => $company_id,
            'status'        => $request->status,
            'published_at'  => now(),
        ];

        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            if (!empty($news->image_path)) {
                $oldImagePath = public_path('storage/news-feed/' . basename($news->image_path));
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
            $filename = uploadFile($request->file('image'), 'public', 'news-feed');

            if (!$filename) {
                return ['success' => false, 'message' => 'Failed to upload image.', 'data' => []];
            }

            $data['image_path'] = asset('storage/news-feed/' . $filename);
        } else {
            $data['image_path'] = $request->input('existing_image_path');
        }

        $news->update($data);

        return true;
    }

    public function getNewsFeed($newsId)
    {
        $news = News::find($newsId);
        if (!$news) {
            return ['success' => false, 'message' => 'News item not found.', 'data' => []];
        }
        return $news;
    }

    public function deleteNewsFeed($newsId)
    {
        $news = News::find($newsId);
        if (!$news) {
            return ['success' => false, 'message' => 'News item not found.', 'data' => []];
        }
        $news->delete();
        return true;
    }

    public function toggleStatus($request, $news_id)
    {
        $status = $request['status'];
        $news = News::find($news_id);

        if (!$news) {
            return [
                'success' => false,
                'message' => 'News not found.'
            ];
        }

        if ($status === 'active') {
            $news->status = 'active';
            $news->published_at = now();
            $message = 'News published successfully.';
        } else {
            $news->status = 'inactive';
            $news->published_at = $news->updated_at;
            $message = 'News unpublished successfully.';
        }

        $news->save();

        return [
            'success' => true,
            'message' => $message
        ];
    }

}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\PostService;
use App\Traits\AppCommonFunction;
use App\Traits\ApiResponse;
use App\Http\Requests\CreatePostRequest;
use App\Models\Admin;

class PostController extends Controller
{
    use AppCommonFunction, ApiResponse;
    public function __construct(private PostService $post_service) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $posts = $this->post_service->getPostsListForAdmin();
        return view('admin.social-feed.index', compact('posts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $companies = $this->getAllCompanies();
        return view('admin.social-feed.create', compact('companies'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreatePostRequest $request)
    {
        try {
            $user = $this->getAnyAuthenticatedUser();
            $response = $this->post_service->createPost($request, $user);
            if ($response['success'] === false) {
                return response()->json([
                    'message' => $response['message']
                ], 500);
            }
            return response()->json([
                'redirect' => route('admin.social-feed.index'),
                'message' => $response['message']
            ]);
        } catch (\Exception $e) {
            $this->error(status: false, message: $e->getMessage(), code: 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $post = $this->post_service->getAdminPostDetail($id);
        return view('admin.social-feed.view', compact('post'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $post = $this->post_service->getAdminPostDetail($id);
        $companies = $this->getAllCompanies();
        return view('admin.social-feed.edit', compact('post', 'companies'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CreatePostRequest $request, string $id)
    {
        try {
            $user = $this->getAnyAuthenticatedUser();
            $response = $this->post_service->updatePost($id, $request, $user);

            if (!$response['success']) {
                return response()->json([
                    'message' => $response['message']
                ], 500);
            }

            return response()->json([
                'redirect' => route('admin.social-feed.list'),
                'message' => $response['message']
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
    public function destroy(string $id)
    {
        $response = $this->post_service->deletePost($id);
        if ($response['success'] === false) {
            return response()->json([
                'message' => $response['message']
            ], 500);
        }
        return response()->json([
            'message' => $response['message']
        ]);
    }

    public function toggleStatus(Request $request, string $id)
    {
        $response = $this->post_service->toggleStatus($request, $id);
        if ($response['success'] === false) {
            return response()->json([
                'message' => $response['message']
            ], 500);
        }
        return response()->json([
            'message' => $response['message']
        ]);
    }

    public function deleteMedia($id)
    {
        $response = $this->post_service->deleteMedia($id);
        if ($response['success'] === false) {
            return response()->json([
                'message' => $response['message']
            ], 500);
        }
        return response()->json([
            'message' => $response['message']
        ]);
    }

    public function getReportedPosts()
    {
        $reported_posts = $this->post_service->getReportedPostsList();
        return view('admin.social-feed.reports', compact('reported_posts'));
    }

    public function reportedUsersList($id)
    {
        $reports = $this->post_service->getReportedUsersList($id);
        return view('admin.social-feed.reported-users', compact('reports'));
    }

    public function reportedUsersDetail($id)
    {
        $report = $this->post_service->getReportedUsersDetails($id);
        return view('admin.social-feed.reported-users-detail', compact('report'));
    }

    public function changeReportStatus(string $id, $action)
    {
        $response = $this->post_service->changeReportStatus($id, $action);
        if ($response['success'] === false) {
            return response()->json([
                'message' => $response['message']
            ], 500);
        }
        return response()->json([
            'message' => $response['message']
        ]);
    }
}

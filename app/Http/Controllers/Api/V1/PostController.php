<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use App\Services\PostService;
use App\Http\Requests\CreatePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Requests\AddCommentRequest;
use App\Http\Requests\ReactToPostRequest;
use App\Http\Requests\ReportPostRequest;
use App\Http\Requests\LikeCommentRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class PostController extends Controller
{
    use ApiResponse;

    public function __construct(private PostService $postService)
    {
        $this->postService = $postService;
    }

    /**
     * Create a new post
     *
     * @param CreatePostRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createPost(CreatePostRequest $request)
    {
        try {
            $user = Auth::user();
            if ($user->isEmployee() && $user->company_id) {
                $request->merge(['company_id' => $user->company_id]);
            }
            $result = $this->postService->createPost($request, $user);

            if ($result['success'] === false) {
                return $this->error(
                    status: false,
                    message: $result['message'],
                    code: 400
                );
            }

            return $this->success(
                status: true,
                message: $result['message'],
                result: $result['data'],
                code: 201
            );
        } catch (\Throwable $th) {
            return $this->error(
                status: false,
                message: $th->getMessage()
            );
        }
    }

    /**
     * Get list of posts
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPostsList()
    {
        try {
            $limit = request()->get('limit', 15);
            $user_id = request()->get('user_id', null);
            $user = Auth::user();
            $companyId = null;
            if ($user_id === null && $user->isEmployee()) {
                $companyId = $user->company_id;
            }
            $posts = $this->postService->getPostsList(
                userId: $user_id,
                limit: $limit,
                companyId: $companyId ?? null
            );
            return $this->success(
                status: true,
                message: 'Posts retrieved successfully.',
                result: $posts
            );
        } catch (\Throwable $th) {
            return $this->error(
                status: false,
                message: $th->getMessage()
            );
        }
    }

    /**
     * Get post detail with comments
     *
     * @param int $postId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPostDetail($postId)
    {
        try {
            $result = $this->postService->getPostDetail($postId);

            if ($result['success'] === false) {
                return $this->error(
                    status: false,
                    message: $result['message'],
                    code: 404
                );
            }

            return $this->success(
                status: true,
                message: $result['message'],
                result: $result['data']
            );
        } catch (\Throwable $th) {
            return $this->error(
                status: false,
                message: $th->getMessage()
            );
        }
    }

    public function getUserPostsList()
    {
        try {
            $limit = request()->get('limit', 15);
            $user = Auth::user();
            $posts = $this->postService->getPostsList(
                userId: $user->id,
                limit: $limit
            );
            return $this->success(
                status: true,
                message: 'User posts retrieved successfully.',
                result: $posts
            );
        } catch (\Throwable $th) {
            return $this->error(
                status: false,
                message: $th->getMessage()
            );
        }
    }

    /**
     * Update an existing post
     *
     * @param int $postId
     * @param UpdatePostRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePost($postId, UpdatePostRequest $request)
    {
        try {
            $user = Auth::user();
            if ($user->isEmployee() && $user->company_id) {
                $request->merge(['company_id' => $user->company_id]);
            }
            $result = $this->postService->updatePost($postId, $request);

            if ($result['success'] === false) {
                return $this->error(
                    status: false,
                    message: $result['message'],
                    code: 400
                );
            }

            return $this->success(
                status: true,
                message: $result['message'],
                result: $result['data']
            );
        } catch (\Throwable $th) {
            return $this->error(
                status: false,
                message: $th->getMessage()
            );
        }
    }

    /**
     * Delete a post
     *
     * @param int $postId
     * @return \Illuminate\Http\JsonResponse
     */
    public function deletePost($postId)
    {
        try {
            $result = $this->postService->deletePost($postId);
            if ($result['success'] === false) {
                return $this->error(
                    status: false,
                    message: $result['message'],
                    code: 404
                );
            }
            return $this->success(
                status: true,
                message: 'Post deleted successfully.'
            );
        } catch (\Throwable $th) {
            return $this->error(
                status: false,
                message: $th->getMessage()
            );
        }
    }

    /**
     * Add a comment to a post (or reply to a comment)
     *
     * @param AddCommentRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addComment(AddCommentRequest $request)
    {
        try {
            $user = Auth::user();
            $result = $this->postService->addComment($request, $user->id);

            if ($result['success'] === false) {
                return $this->error(
                    status: false,
                    message: $result['message'],
                    code: 400
                );
            }

            return $this->success(
                status: true,
                message: $result['message'],
                result: $result['data'],
                code: 201
            );
        } catch (\Throwable $th) {
            return $this->error(
                status: false,
                message: $th->getMessage()
            );
        }
    }

    /**
     * Toggle reaction on a post (add/update/remove)
     *
     * @param ReactToPostRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function reactToPost(ReactToPostRequest $request)
    {
        try {
            $user = Auth::user();
            $result = $this->postService->toggleReaction($request, $user->id);

            if ($result['success'] === false) {
                return $this->error(
                    status: false,
                    message: $result['message'],
                    code: 400
                );
            }

            return $this->success(
                status: true,
                message: $result['message'],
                result: $result['data']
            );
        } catch (\Throwable $th) {
            return $this->error(
                status: false,
                message: $th->getMessage()
            );
        }
    }

    public function deletePostMedia($postMediaId)
    {
        try {
            $result = $this->postService->deletePostMediaById($postMediaId);
            if ($result['success'] === false) {
                return $this->error(
                    status: false,
                    message: $result['message'],
                    code: 404
                );
            }
            return $this->success(
                status: true,
                message: 'Post media deleted successfully.'
            );
        } catch (\Throwable $th) {
            return $this->error(
                status: false,
                message: $th->getMessage()
            );
        }
    }

    /**
     * Report a post
     *
     * @param ReportPostRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function reportPost(ReportPostRequest $request)
    {
        try {
            $user = Auth::user();
            $result = $this->postService->reportPost($request, $user->id);

            if ($result['success'] === false) {
                return $this->error(
                    status: false,
                    message: $result['message'],
                    code: 400
                );
            }

            return $this->success(
                status: true,
                message: $result['message'],
                result: $result['data'],
                code: 201
            );
        } catch (\Throwable $th) {
            return $this->error(
                status: false,
                message: $th->getMessage()
            );
        }
    }

    /**
     * Toggle like on a comment (like/unlike)
     *
     * @param LikeCommentRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function likeComment(LikeCommentRequest $request)
    {
        try {
            $user = Auth::user();
            $result = $this->postService->likeComment($request, $user->id);

            if ($result['success'] === false) {
                return $this->error(
                    status: false,
                    message: $result['message'],
                    code: 400
                );
            }

            return $this->success(
                status: true,
                message: $result['message'],
                result: $result['data']
            );
        } catch (\Throwable $th) {
            return $this->error(
                status: false,
                message: $th->getMessage()
            );
        }
    }
}

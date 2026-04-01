<?php

namespace App\Services;

use App\Enums\PostStatusEnum;
use App\Models\Post;
use App\Models\PostMedia;
use App\Models\PostComment;
use App\Models\PostReaction;
use App\Models\PostReport;
use App\Models\CommentLike;
use App\Http\Resources\PostResource;
use App\Http\Resources\PostDetailResource;
use App\Http\Resources\PostCommentResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Traits\AppCommonFunction;

class PostService
{
    use AppCommonFunction;
    /**
     * Create a new post with media
     *
     * @param \App\Http\Requests\CreatePostRequest $request
     * @param \App\Models\User $user
     * @return array
     */
    public function createPost($request, $user): array
    {
        DB::beginTransaction();

        try {
            // Create the post
            $post = Post::create([
                'author_id' => $user->id,
                'author_type' => get_class($user), // App\Models\User
                'content' => $request->content ?? null,
                'published_at' => now(),
                'status' => PostStatusEnum::APPROVED->value, // Posts require admin approval
                'company_id' => $request->company_id ?? null,
            ]);

            // Handle media uploads if provided
            if ($request->hasFile('medias')) {
                $this->handleMediaUploads($post, $request->file('medias'));
            }

            DB::commit();

            // Load relationships for response
            $post->load(['media', 'author']);

            return [
                'success' => true,
                'message' => __('general.post_created'),
                'data' => new PostResource($post)
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Post creation failed: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => __('general.post_creation_failed'),
                'data' => []
            ];
        }
    }

    /**
     * Handle multiple media uploads
     *
     * @param Post $post
     * @param array $mediaFiles
     * @return void
     */
    private function handleMediaUploads(Post $post, array $mediaFiles): void
    {
        foreach ($mediaFiles as $index => $file) {
            $mediaType = $this->detectMediaType($file);
            $filename = $this->uploadMediaFile($file, $mediaType, $index);

            if ($filename) {
                PostMedia::create([
                    'post_id' => $post->id,
                    'media_type' => $mediaType,
                    'file_path' => "PostMedia/{$filename}",
                    'file_size' => round($file->getSize() / 1024), // KB
                    'mime_type' => $file->getMimeType(),
                    'order' => $index,
                ]);
            }
        }
    }

    /**
     * Detect media type from file
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @return string
     */
    private function detectMediaType($file): string
    {
        $mimeType = $file->getMimeType();
        $extension = strtolower($file->getClientOriginalExtension());

        if (str_starts_with($mimeType, 'image/')) {
            return 'image';
        }

        if (str_starts_with($mimeType, 'video/')) {
            return 'video';
        }

        if ($mimeType === 'application/pdf' || $extension === 'pdf') {
            return 'pdf';
        }

        return 'image'; // Default
    }

    /**
     * Upload media file to storage
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $mediaType
     * @return string|null
     */
    private function uploadMediaFile($file, string $mediaType, int $index): ?string
    {
        try {
            // Generate a unique filename using uniqid, index, and timestamp
            $extension = $file->getClientOriginalExtension();
            $uniqueFilename = uniqid("media_{$index}_", true) . '.' . $extension;

            // Store the file with the unique filename
            $path = $file->storeAs('PostMedia', $uniqueFilename, 'public');

            // Return just the filename
            return basename($path);
        } catch (\Exception $e) {
            Log::error('Media upload failed: ' . $e->getMessage());
            return null;
        }
    }

    public function getPostsList($userId = null, $limit = 15, $companyId = null)
    {
        $query = Post::with(['media', 'author', 'reactions'])
            ->withCount(['comments', 'reactions'])
            ->whereDoesntHave('reports', function ($query) {
                $query->where('reported_by', auth()->id());
            })
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->orderBy('updated_at', 'desc')
            ->orderBy('published_at', 'desc');
        if ($userId) {
            $query->where('author_id', $userId);
        } else {
            $query->approved()
                ->whereNotNull('published_at');
        }
        if ($companyId) {
            $query->where('company_id', $companyId);
        } else {
            $query->whereNull('company_id');
        }
        $posts = $query->paginate($limit);
        $data = PostResource::collection($posts->items());
        return paginationData(
            data: $data,
            total: $posts->total(),
            perPage: $posts->perPage(),
            currentPage: $posts->currentPage(),
            lastPage: $posts->lastPage(),
            from: $posts->firstItem(),
            to: $posts->lastItem()
        );
    }

    /**
     * Get post detail with comments
     *
     * @param int $postId
     * @return array
     */
    public function getPostDetail($postId): array
    {
        try {
            $post = Post::with([
                'media',
                'author',
                'reactions',
                'comments' => function ($query) {
                    $query->whereNull('parent_comment_id')
                        ->with(['user', 'replies.user']);
                }
            ])
                ->whereDoesntHave('reports') // Exclude reported posts
                ->approved()
                ->whereNotNull('published_at')
                ->find($postId);

            if (!$post) {
                return [
                    'success' => false,
                    'message' => __('general.post_not_found'),
                    'data' => null
                ];
            }

            return [
                'success' => true,
                'message' => __('general.post_details'),
                'data' => new PostDetailResource($post)
            ];
        } catch (\Exception $e) {
            Log::error('Post detail fetch failed: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => __('general.post_details_failed'),
                'data' => null
            ];
        }
    }

    /**
     * Update an existing post
     *
     * @param int $id
     * @param \App\Http\Requests\UpdatePostRequest $request
     * @return array
     */
    public function updatePost($id, $request, $user = null): array
    {
        DB::beginTransaction();

        try {
            $author_id = $user ? $user->id : $request->user()->id;
            $post = Post::where('author_id', $author_id)->find($id);

            if (!$post) {
                return [
                    'success' => false,
                    'message' => __('general.post_not_found'),
                    'data' => []
                ];
            }
            // Update content if provided
            if ($request->has('content')) {
                $post->content = $request->content;
            }

            // Handle media updates if new media files are provided
            if ($request->has('medias') && is_array($request->file('medias'))) {
                $this->handleMediaUploads($post, $request->file('medias'));
            }

            $post->save();
            DB::commit();

            // Load relationships for response
            $post->load(['media', 'author']);

            return [
                'success' => true,
                'message' => __('general.post_updated'),
                'data' => new PostResource($post)
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Post update failed: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => __('general.post_update_failed'),
                'data' => []
            ];
        }
    }

    /**
     * Delete all media files associated with a post
     *
     * @param Post $post
     * @return void
     */
    private function deletePostMedia(Post $post): void
    {
        try {
            $mediaFiles = $post->media;

            foreach ($mediaFiles as $media) {
                // Delete file from storage first
                if ($media->file_path) {
                    $fullPath = storage_path('app/public/' . $media->file_path);
                    // dd($fullPath);
                    if (file_exists($fullPath)) {
                        Storage::disk('public')->delete($media->file_path);
                    }
                }

                // Delete from database
                $media->delete();
            }
        } catch (\Exception $e) {
            Log::error('Media deletion failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function deletePost($postId): array
    {
        try {
            $post = Post::find($postId);
            if (!$post) {
                return [
                    'success' => false,
                    'message' => __('general.post_not_found'),
                    'data' => []
                ];
            }

            // Delete associated media files
            $this->deletePostMedia($post);

            // Delete the post
            $post->delete();

            return [
                'success' => true,
                'message' => __('general.post_deleted'),
                'data' => []
            ];
        } catch (\Exception $e) {
            Log::error('Post deletion failed: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => __('general.post_deletion_failed'),
                'data' => []
            ];
        }
    }

    /**
     * Add a comment to a post (or reply to a comment)
     *
     * @param \App\Http\Requests\AddCommentRequest $request
     * @param int $userId
     * @return array
     */
    public function addComment($request, $userId): array
    {
        try {
            // Create the comment
            $comment = PostComment::create([
                'post_id' => $request->post_id,
                'user_id' => $userId,
                'comment' => $request->comment,
                'parent_comment_id' => $request->parent_comment_id,
            ]);

            if ($request->has('mention_ids') && is_array($request->mention_ids)) {
                $authUser = $request->user();
                NotificationService::sendMentionNotifications($authUser, $request->mention_ids, $comment);
            }

            // Load relationships for response
            $comment->load(['user', 'replies.user']);

            return [
                'success' => true,
                'message' => $request->parent_comment_id
                    ? __('general.reply_added')
                    : __('general.comment_added'),
                'data' => new PostCommentResource($comment)
            ];
        } catch (\Exception $e) {
            Log::error('Comment creation failed: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => __('general.reply_addition_failed'),
                'data' => null
            ];
        }
    }

    /**
     * Toggle reaction on a post (add or remove)
     * If reaction_type is provided, update or create
     * If reaction_type is null, remove reaction
     *
     * @param \App\Http\Requests\ReactToPostRequest $request
     * @param int $userId
     * @return array
     */
    public function toggleReaction($request, $userId): array
    {
        try {
            $postId = $request->post_id;
            $reactionType = $request->reaction_type ?? '❤️'; // Default to heart if not provided

            // Check if post exists and is approved
            $post = Post::approved()
                ->whereNotNull('published_at')
                ->find($postId);

            if (!$post) {
                return [
                    'success' => false,
                    'message' => __('general.post_not_found'),
                    'data' => null
                ];
            }

            // Check if user already reacted to this post
            $existingReaction = PostReaction::where('post_id', $postId)
                ->where('user_id', $userId)
                ->first();

            // If reaction exists
            if ($existingReaction) {
                // If same reaction type, remove it (unreact)
                if ($existingReaction->reaction_type === $reactionType) {
                    $existingReaction->delete();

                    return [
                        'success' => true,
                        'message' => __('general.reaction_removed'),
                        'data' => [
                            'reacted' => false,
                            'reaction_type' => null,
                            'reactions_count' => $post->reactions()->count(),
                        ]
                    ];
                }

                // If different reaction type, update it
                $existingReaction->update(['reaction_type' => $reactionType]);

                return [
                    'success' => true,
                    'message' => __('general.reaction_updated'),
                    'data' => [
                        'reacted' => true,
                        'reaction_type' => $reactionType,
                        'reactions_count' => $post->reactions()->count(),
                    ]
                ];
            }

            // Create new reaction
            PostReaction::create([
                'post_id' => $postId,
                'user_id' => $userId,
                'reaction_type' => $reactionType,
            ]);

            return [
                'success' => true,
                'message' => __('general.reaction_added'),
                'data' => [
                    'reacted' => true,
                    'reaction_type' => $reactionType,
                    'reactions_count' => $post->reactions()->count(),
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Reaction toggle failed: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => __('general.reaction_failed'),
                'data' => null
            ];
        }
    }

    public function deletePostMediaById($mediaId): array
    {
        try {
            $media = PostMedia::find($mediaId);
            if (!$media) {
                return [
                    'success' => false,
                    'message' => __('general.media_not_found'),
                    'data' => []
                ];
            }

            // Delete file from storage
            if ($media->file_path) {
                $fullPath = storage_path('app/public/' . $media->file_path);
                if (file_exists($fullPath)) {
                    Storage::disk('public')->delete($media->file_path);
                }
            }

            // Delete from database
            $media->delete();

            return [
                'success' => true,
                'message' => __('general.media_deleted'),
                'data' => []
            ];
        } catch (\Exception $e) {
            Log::error('Media deletion failed: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => __('general.media_deletion_failed'),
                'data' => []
            ];
        }
    }

    /**
     * Report a post
     *
     * @param \App\Http\Requests\ReportPostRequest $request
     * @param int $userId
     * @return array
     */
    public function reportPost($request, $userId): array
    {
        try {
            $post = Post::find($request->post_id);

            if (!$post) {
                return [
                    'success' => false,
                    'message' => __('general.post_not_found'),
                    'data' => null
                ];
            }

            // Create the report
            PostReport::create([
                'post_id' => $request->post_id,
                'reported_by' => $userId,
                'reason' => $request->reason,
                'description' => $request->description,
                'status' => 'pending',
            ]);

            return [
                'success' => true,
                'message' => __('general.post_reported'),
                'data' => [
                    'post_id' => $post->id,
                    'reason' => $request->reason,
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Post report failed: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => __('general.post_report_failed'),
                'data' => null
            ];
        }
    }

    /**
     * Toggle like on a comment (like/unlike)
     *
     * @param \App\Http\Requests\LikeCommentRequest $request
     * @param int $userId
     * @return array
     */
    public function likeComment($request, $userId): array
    {
        try {
            $comment = PostComment::find($request->comment_id);

            if (!$comment) {
                return [
                    'success' => false,
                    'message' => __('general.comment_not_found'),
                    'data' => null
                ];
            }

            // Check if user already liked this comment
            $existingLike = CommentLike::where('comment_id', $request->comment_id)
                ->where('user_id', $userId)
                ->first();

            if ($existingLike) {
                // Unlike: Remove the like
                $existingLike->delete();

                return [
                    'success' => true,
                    'message' => __('general.comment_unliked'),
                    'data' => [
                        'liked' => false,
                        'likes_count' => $comment->likes()->count(),
                    ]
                ];
            }

            // Like: Add new like
            CommentLike::create([
                'comment_id' => $request->comment_id,
                'user_id' => $userId,
            ]);

            return [
                'success' => true,
                'message' => __('general.comment_liked'),
                'data' => [
                    'liked' => true,
                    'likes_count' => $comment->likes()->count(),
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Comment like toggle failed: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => __('general.comment_like_failed'),
                'data' => null
            ];
        }
    }

    public function getPostsListForAdmin()
    {
        $query = Post::query()
            ->with(['author', 'media'])
            ->where('author_type', \App\Models\Admin::class)
            ->whereDoesntHave('reports')
            ->whereNotNull('published_at')
            ->latest();

        return $this->getPaginatedData($query);
    }

    public function getPostsListForCompanyAdmin($companyId)
    {
        $query = Post::query()
            ->with(['author', 'media'])
            // ->where('author_type', \App\Models\User::class)
            ->where('company_id', $companyId)
            ->whereDoesntHave('reports')
            ->whereNotNull('published_at')
            ->latest();

        return $this->getPaginatedData($query);
    }

    public function toggleStatus($request, $post_id)
    {
        $status = $request['status'];
        $post = Post::find($post_id);
        if (!$post) {
            return [
                'success' => false,
                'message' => __('general.post_not_found'),
                'data' => []
            ];
        }

        if ($status === 'active') {
            $post->status = PostStatusEnum::APPROVED->value;
            $post->published_at = now();
            $message = __('general.post_published');
        } else {
            $post->status = PostStatusEnum::REJECTED->value;
            $post->published_at = $post->updated_at;
            $message = __('general.post_unpublished');
        }

        $post->save();

        return [
            'success' => true,
            'message' => $message
        ];
    }

    public function deleteMedia($mediaId)
    {
        $media = PostMedia::find($mediaId);
        if (!$media) {
            return [
                'success' => false,
                'message' => __('general.media_not_found'),
                'data' => []
            ];
        }

        // Delete file from storage
        if ($media->file_path) {
            $fullPath = storage_path('app/public/' . $media->file_path);
            if (file_exists($fullPath)) {
                Storage::disk('public')->delete($media->file_path);
            }
        }

        // Delete from database
        $media->delete();

        return [
            'success' => true,
            'message' => __('general.media_deleted'),
            'data' => []
        ];
    }

    public function getAdminPostDetail($postId): array
    {
        try {
            $post = Post::with([
                'media',
                'author',
                'reactions',
                'comments' => function ($query) {
                    $query->whereNull('parent_comment_id')
                        ->with(['user', 'replies.user'])
                        ->orderBy('created_at', 'desc');
                }
            ])
                ->whereNotNull('published_at')
                ->find($postId);

            if (!$post) {
                return [
                    'success' => false,
                    'message' => __('general.post_not_found'),
                    'data' => null
                ];
            }

            return [
                'success' => true,
                'message' => __('general.post_details'),
                'data' => new PostDetailResource($post)
            ];
        } catch (\Exception $e) {
            Log::error('Post detail fetch failed: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => __('general.post_details_failed'),
                'data' => null
            ];
        }
    }

    public function getReportedPostsList($companyId = null)
    {
        $query = Post::query()
            ->withCount([
                'reports as reports_count' => function ($q) {
                    $q->where('status', 'pending');
                }
            ])
            ->whereHas('reports', function ($query) {
                $query->where('status', 'pending');
            })
            ->latest();
        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        return $this->getPaginatedData($query);
    }

    public function getReportListCount($companyId = null)
    {
        $query = PostReport::query()
            ->where('status', 'pending');
        if ($companyId) {
            $query->whereHas('post', function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            });
        }
        return $query->count();
    }

    public function getReportedUsersList($id)
    {
        $query = PostReport::query()
            ->with(['post', 'reporter', 'reviewer'])
            ->where('post_id', $id)
            ->latest();

        return $this->getPaginatedData($query);
    }

    public function getReportedUsersDetails($id)
    {
        $report = PostReport::find($id);
        return $report;
    }

    public function changeReportStatus($post_id, $status)
    {
        $post = Post::with('reports')->find($post_id);

        if (!$post) {
            return [
                'success' => false,
                'message' => __('general.post_not_found'),
                'data' => []
            ];
        }

        if ($post->reports->count() === 0) {
            return [
                'success' => false,
                'message' => __('general.report_not_found'),
                'data' => []
            ];
        }

        $reviewed_by = $this->getAnyAuthenticatedUser();
        // dd($post->reports);
        foreach ($post->reports as $report) {
            $report->status = $status;
            $report->reviewed_at = now();
            $report->reviewed_by = $reviewed_by->id;
            if ($reviewed_by instanceof \App\Models\Admin) {
                $report->reviewed_by_type  = \App\Models\Admin::class;
            } else {
                $report->reviewed_by_type  = \App\Models\User::class;
            }
            $report->reviewed_by_id = $reviewed_by->id;
            $report->save();
        }

        if ($status === 'resolved') {
            $post->delete();
        } else {
            $post->reports()->each(function ($report) {
                $report->delete();
            });
        }

        return [
            'success' => true,
            'message' => __('general.report_status_updated')
        ];
    }
}
